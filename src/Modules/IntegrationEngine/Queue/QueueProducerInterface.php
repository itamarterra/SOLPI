<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Queue;

interface QueueProducerInterface
{
    /**
     * @param array<string,mixed> $payload
     */
    public function enqueue(string $name, string $handler, array $payload, int $maxAttempts = 3): int;
}
