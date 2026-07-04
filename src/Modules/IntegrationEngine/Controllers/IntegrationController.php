<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Controllers;

use SOLPI\Modules\IntegrationEngine\Services\IntegrationOrchestratorService;
use SOLPI\Modules\IntegrationEngine\Services\IntegrationSummaryService;
use SOLPI\Modules\IntegrationEngine\Services\QueueService;

final class IntegrationController
{
    private IntegrationOrchestratorService $orchestrator;
    private QueueService $queue;
    private IntegrationSummaryService $summary;

    public function __construct()
    {
        $this->orchestrator = new IntegrationOrchestratorService();
        $this->queue = new QueueService();
        $this->summary = new IntegrationSummaryService();
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function ingest(string $source, string $event, array $payload): array
    {
        return $this->orchestrator->ingest($source, $event, $payload);
    }

    /**
     * @return array<int,string>
     */
    public function supportedAdapters(): array
    {
        return $this->orchestrator->supportedAdapters();
    }

    /**
     * @param array<string,mixed> $adapterPayload
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function ingestViaAdapter(string $source, string $event, string $adapter, array $adapterPayload, array $context = []): array
    {
        return $this->orchestrator->ingestViaAdapter($source, $event, $adapter, $adapterPayload, $context);
    }

    /**
     * @return array<string,mixed>
     */
    public function jobs(int $limit = 30): array
    {
        return [
            'items' => $this->queue->recent($limit),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        return $this->summary->summary();
    }
}
