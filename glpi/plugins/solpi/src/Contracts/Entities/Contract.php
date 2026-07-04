<?php
declare(strict_types=1);

namespace SOLPI\Contracts\Entities;

final class Contract
{
    public function __construct(
        public readonly int $id,
        public readonly string $number,
        public readonly int $companyId,
        public readonly string $startDate,
        public readonly ?string $endDate = null
    ) {}
}

