<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\EntityResolver;

interface EntityResolverInterface
{
    /**
     * @return array<string,mixed>
     */
    public function resolve(array $record): array;
}
