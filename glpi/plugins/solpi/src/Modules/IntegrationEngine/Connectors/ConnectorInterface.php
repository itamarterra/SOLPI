<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Connectors;

interface ConnectorInterface
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function pull(array $context = []): array;
}
