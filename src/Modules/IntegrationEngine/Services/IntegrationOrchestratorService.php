<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use InvalidArgumentException;
use SOLPI\Modules\IntegrationEngine\Adapters\AdapterFactory;
use SOLPI\Modules\IntegrationEngine\DTO\IngestionEnvelope;
use SOLPI\Modules\IntegrationEngine\Validators\PayloadValidator;

final class IntegrationOrchestratorService
{
    private IdempotencyService $idempotency;
    private QueueService $queue;
    private AuditService $audit;
    private PayloadValidator $validator;
    private AdapterFactory $adapterFactory;
    private SourceCheckpointService $checkpoints;
    private BatchContextService $batchContext;

    public function __construct()
    {
        $this->idempotency = new IdempotencyService();
        $this->queue = new QueueService();
        $this->audit = new AuditService();
        $this->validator = new PayloadValidator();
        $this->adapterFactory = new AdapterFactory();
        $this->checkpoints = new SourceCheckpointService();
        $this->batchContext = new BatchContextService();
    }

    /**
     * @return array<int,string>
     */
    public function supportedAdapters(): array
    {
        return $this->adapterFactory->supported();
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function ingest(string $source, string $event, array $payload): array
    {
        $envelope = new IngestionEnvelope($source, $event, $payload);
        $data = $envelope->toArray();

        $this->validator->validate($data);

        if ($this->idempotency->isDuplicate($envelope->source, $envelope->idempotencyKey)) {
            $this->idempotency->markDuplicate($envelope->source, $envelope->idempotencyKey, $data);
            $this->audit->warning('Envelope duplicate ignored.', [
                'source' => $envelope->source,
                'event' => $envelope->event,
                'idempotency_key' => $envelope->idempotencyKey,
                'correlation_id' => $envelope->correlationId,
            ]);

            return [
                'status' => 'duplicate',
                'idempotency_key' => $envelope->idempotencyKey,
                'correlation_id' => $envelope->correlationId,
            ];
        }

        $this->idempotency->markReceived($envelope->source, $envelope->idempotencyKey, $data);
        $jobId = $this->queue->push('integration:' . $envelope->source . ':' . $envelope->event, $data);

        $this->audit->info('Envelope enqueued for processing.', [
            'source' => $envelope->source,
            'event' => $envelope->event,
            'idempotency_key' => $envelope->idempotencyKey,
            'correlation_id' => $envelope->correlationId,
            'job_id' => $jobId,
        ]);

        return [
            'status' => 'queued',
            'job_id' => $jobId,
            'idempotency_key' => $envelope->idempotencyKey,
            'correlation_id' => $envelope->correlationId,
        ];
    }

    /**
     * @param array<string,mixed> $adapterPayload
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function ingestViaAdapter(string $source, string $event, string $adapter, array $adapterPayload, array $context = []): array
    {
        $adapter = strtolower(trim($adapter));
        $checkpointContext = is_array($context['checkpoint'] ?? null) ? $context['checkpoint'] : [];
        $checkpointEnabled = (bool)($checkpointContext['enabled'] ?? false);
        $checkpointName = (string)($checkpointContext['name'] ?? 'default');
        $checkpointIn = null;

        if ($checkpointEnabled) {
            $saved = $this->checkpoints->get($source, $adapter, $checkpointName);
            if (is_array($saved) && isset($saved['last_value']) && (string)$saved['last_value'] !== '') {
                $checkpointIn = (string)$saved['last_value'];
                $adapterPayload = $this->applyCheckpointInbound($adapterPayload, $adapter, $checkpointIn);
            }
        }

        try {
            $instance = $this->adapterFactory->make($adapter);
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'supported_adapters' => $this->supportedAdapters(),
            ];
        }

        $result = $instance->ingest($adapterPayload, $context);
        $records = $result['records'] ?? [];
        if (!is_array($records)) {
            $records = [];
        }

        $requestedMax = (int)($context['max_records'] ?? $adapterPayload['max_records'] ?? 2000);
        $maxRecords = max(1, min(20000, $requestedMax));
        $truncated = false;

        if (count($records) > $maxRecords) {
            $records = array_slice($records, 0, $maxRecords);
            $truncated = true;
        }

        $checkpointOut = null;
        $checkpointSaved = false;
        if ($checkpointEnabled) {
            $checkpointOut = $this->extractCheckpointOutbound($records, $result, $adapterPayload, $adapter);
            if ($checkpointOut !== null && $checkpointOut !== '') {
                $this->checkpoints->set($source, $adapter, $checkpointName, (string)$checkpointOut, [
                    'records_total' => count($records),
                    'truncated' => $truncated,
                    'event' => $event,
                    'updated_by' => 'ingestViaAdapter',
                ]);
                $checkpointSaved = true;
            }
        }

        $batchJobs = [];
        $recordsForBatch = [];
        $duplicates = 0;

        foreach ($records as $index => $record) {
            if (!is_array($record)) {
                $record = [
                    'value' => $record,
                ];
            }

            $enriched = $record;
            $enriched['_adapter'] = $adapter;
            $enriched['_adapter_meta'] = is_array($result['meta'] ?? null) ? $result['meta'] : [];
            $enriched['_record_index'] = $index;
            $recordsForBatch[] = $enriched;
        }

        foreach ($recordsForBatch as $record) {
            $envelope = new IngestionEnvelope($source, $event, $record);
            $data = $envelope->toArray();

            if ($this->idempotency->isDuplicate($envelope->source, $envelope->idempotencyKey)) {
                $this->idempotency->markDuplicate($envelope->source, $envelope->idempotencyKey, $data);
                $duplicates++;
                continue;
            }

            $this->idempotency->markReceived($envelope->source, $envelope->idempotencyKey, $data);
            $batchJobs[] = [
                'name' => 'integration:' . $envelope->source . ':' . $envelope->event,
                'handler' => 'IntegrationEngineWorker@process',
                'payload' => $data,
                'max_attempts' => 5,
            ];
        }

        $requestedBatchSize = (int)($context['batch_size'] ?? $adapterPayload['batch_size'] ?? 250);
        $batchSize = max(1, min(1000, $requestedBatchSize));
        $jobIds = [];
        $batchCount = 0;
        $batchChunks = array_chunk($batchJobs, $batchSize);
        $batchTotal = count($batchChunks);
        $ingestionRunId = date('YmdHis') . '-' . bin2hex(random_bytes(4));

        foreach ($batchChunks as $batchIndex => $jobChunk) {
            $annotatedChunk = [];

            foreach ($jobChunk as $jobIndex => $job) {
                $job['payload']['_queue_meta'] = $this->batchContext->build(
                    $adapter,
                    $source,
                    $event,
                    $batchSize,
                    $batchIndex,
                    $batchCount + 1,
                    $batchTotal,
                    $jobIndex,
                    count($jobChunk),
                    count($records),
                    count($batchJobs),
                    $duplicates,
                    $truncated,
                    $checkpointEnabled ? [
                        'enabled' => true,
                        'name' => $checkpointName,
                        'in' => $checkpointIn,
                        'out' => $checkpointOut,
                    ] : []
                );
                $job['payload']['_queue_meta']['ingestion_run_id'] = $ingestionRunId;

                $annotatedChunk[] = $job;
            }

            $annotatedChunkCount = count($annotatedChunk);
            foreach ($annotatedChunk as &$job) {
                $job['payload']['_queue_meta']['batch_count'] = $batchCount + 1;
                $job['payload']['_queue_meta']['batch_jobs_in_chunk'] = $annotatedChunkCount;
            }
            unset($job);

            $chunkIds = $this->queue->pushBatch($annotatedChunk);
            $jobIds = array_merge($jobIds, $chunkIds);
            $batchCount++;
        }

        return [
            'status' => 'queued',
            'adapter' => $adapter,
            'source' => $source,
            'event' => $event,
            'records_total' => count($records),
            'records_queued' => count($jobIds),
            'records_duplicate' => $duplicates,
            'job_ids' => $jobIds,
            'batch_size' => $batchSize,
            'batch_count' => $batchCount,
            'batch_total' => $batchTotal,
            'truncated' => $truncated,
            'max_records' => $maxRecords,
            'checkpoint_enabled' => $checkpointEnabled,
            'checkpoint_name' => $checkpointName,
            'checkpoint_in' => $checkpointIn,
            'checkpoint_out' => $checkpointOut,
            'checkpoint_saved' => $checkpointSaved,
            'meta' => is_array($result['meta'] ?? null) ? $result['meta'] : [],
        ];
    }

    /**
     * @param array<string,mixed> $adapterPayload
     * @return array<string,mixed>
     */
    private function applyCheckpointInbound(array $adapterPayload, string $adapter, string $checkpoint): array
    {
        if ($adapter === 'rest') {
            $pagination = is_array($adapterPayload['pagination'] ?? null) ? $adapterPayload['pagination'] : [];
            $mode = strtolower((string)($pagination['mode'] ?? ''));
            if ($mode === 'cursor' && !isset($pagination['start_cursor'])) {
                $pagination['start_cursor'] = $checkpoint;
                $adapterPayload['pagination'] = $pagination;
            }

            return $adapterPayload;
        }

        if ($adapter === 'sql') {
            $incremental = is_array($adapterPayload['incremental'] ?? null) ? $adapterPayload['incremental'] : [];
            $enabled = (bool)($incremental['enabled'] ?? false);
            if ($enabled && !isset($incremental['value'])) {
                $incremental['value'] = $checkpoint;
                $adapterPayload['incremental'] = $incremental;
            }

            return $adapterPayload;
        }

        if ($adapter === 'ldap') {
            $filter = (string)($adapterPayload['filter'] ?? '(objectClass=*)');
            if (str_contains($filter, '{checkpoint}')) {
                $adapterPayload['filter'] = str_replace('{checkpoint}', $checkpoint, $filter);
            }

            return $adapterPayload;
        }

        return $adapterPayload;
    }

    /**
     * @param array<int,mixed> $records
     * @param array<string,mixed> $result
     * @param array<string,mixed> $adapterPayload
     */
    private function extractCheckpointOutbound(array $records, array $result, array $adapterPayload, string $adapter): mixed
    {
        $meta = is_array($result['meta'] ?? null) ? $result['meta'] : [];
        if (isset($meta['checkpoint_out']) && (string)$meta['checkpoint_out'] !== '') {
            return (string)$meta['checkpoint_out'];
        }

        if ($adapter === 'rest' && isset($meta['last_cursor']) && (string)$meta['last_cursor'] !== '') {
            return (string)$meta['last_cursor'];
        }

        if ($adapter === 'sql' && isset($meta['max_incremental_value']) && (string)$meta['max_incremental_value'] !== '') {
            return (string)$meta['max_incremental_value'];
        }

        $fieldPath = (string)($adapterPayload['checkpoint_field'] ?? '');
        if ($fieldPath === '') {
            return null;
        }

        $max = null;
        foreach ($records as $row) {
            if (!is_array($row)) {
                continue;
            }

            $value = $this->valueByPath($row, $fieldPath);
            if ($value === null || $value === '') {
                continue;
            }

            if ($max === null || (string)$value > (string)$max) {
                $max = $value;
            }
        }

        return $max;
    }

    private function valueByPath(array $data, string $path): mixed
    {
        $current = $data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
