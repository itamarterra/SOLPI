<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

final class BatchContextService
{
    /**
     * @param array<string,mixed> $checkpoint
     * @return array<string,mixed>
     */
    public function build(
        string $adapter,
        string $source,
        string $event,
        int $batchSize,
        int $batchIndex,
        int $batchCount,
        int $batchTotal,
        int $jobIndex,
        int $jobsInChunk,
        int $recordsTotal,
        int $recordsQueued,
        int $recordsDuplicate,
        bool $truncated,
        array $checkpoint = []
    ): array {
        $meta = [
            'adapter' => $adapter,
            'source' => $source,
            'event' => $event,
            'batch_size' => $batchSize,
            'batch_index' => $batchIndex,
            'batch_count' => $batchCount,
            'batch_total' => $batchTotal,
            'batch_jobs_in_chunk' => $jobsInChunk,
            'job_index' => $jobIndex,
            'records_total' => $recordsTotal,
            'records_queued' => $recordsQueued,
            'records_duplicate' => $recordsDuplicate,
            'truncated' => $truncated,
        ];

        if ($checkpoint !== []) {
            $meta['checkpoint_enabled'] = (bool)($checkpoint['enabled'] ?? false);
            $meta['checkpoint_name'] = $checkpoint['name'] ?? null;
            $meta['checkpoint_in'] = $checkpoint['in'] ?? null;
            $meta['checkpoint_out'] = $checkpoint['out'] ?? null;
        }

        return $meta;
    }
}