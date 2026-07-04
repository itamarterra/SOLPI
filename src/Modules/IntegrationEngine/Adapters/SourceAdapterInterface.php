<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

interface SourceAdapterInterface
{
    /**
     * @return array<string,mixed>
     */
    public function ingest(array $payload, array $context = []): array;
}
