<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Queue;

use SOLPI\Modules\IntegrationEngine\Repositories\IntegrationJobRepository;

final class DatabaseQueue implements QueueProducerInterface, QueueConsumerInterface
{
    private IntegrationJobRepository $jobs;

    public function __construct()
    {
        $this->jobs = new IntegrationJobRepository();
    }

    public function enqueue(string $name, string $handler, array $payload, int $maxAttempts = 3): int
    {
        return $this->jobs->create($name, $handler, $payload, $maxAttempts);
    }

    public function pending(int $limit = 50): array
    {
        return $this->jobs->pending($limit);
    }
}
