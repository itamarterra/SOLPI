<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Normalizers;

interface NormalizerInterface
{
    /**
     * @return array<string,mixed>
     */
    public function normalize(array $record): array;
}
