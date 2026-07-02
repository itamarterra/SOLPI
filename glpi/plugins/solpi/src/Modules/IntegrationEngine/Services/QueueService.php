<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use SOLPI\Modules\IntegrationEngine\Repositories\JobRepository;

final class QueueService
{
    private JobRepository $jobs;

    public function __construct()
    {
        $this->jobs = new JobRepository();
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function push(string $name, array $payload, string $handler = 'IntegrationEngineWorker@process'): int
    {
        return $this->jobs->enqueue($name, $handler, $payload, 5);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 30): array
    {
        return $this->jobs->recent($limit);
    }
}
