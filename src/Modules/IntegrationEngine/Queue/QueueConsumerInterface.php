<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Queue;

interface QueueConsumerInterface
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array;
}
