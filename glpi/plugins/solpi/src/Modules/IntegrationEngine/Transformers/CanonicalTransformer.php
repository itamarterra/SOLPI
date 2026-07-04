<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Transformers;

final class CanonicalTransformer
{
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function toCanonical(array $payload): array
    {
        return $payload;
    }
}
