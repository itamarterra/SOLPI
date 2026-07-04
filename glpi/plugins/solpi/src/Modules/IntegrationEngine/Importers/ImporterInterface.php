<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Importers;

interface ImporterInterface
{
    /**
     * @return array<string,mixed>
     */
    public function import(array $record, array $context = []): array;
}
