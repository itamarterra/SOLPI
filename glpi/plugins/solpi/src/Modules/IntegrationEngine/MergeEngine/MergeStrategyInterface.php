<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\MergeEngine;

interface MergeStrategyInterface
{
    /**
     * @return array<string,mixed>
     */
    public function merge(array $current, array $incoming, array $context = []): array;
}
