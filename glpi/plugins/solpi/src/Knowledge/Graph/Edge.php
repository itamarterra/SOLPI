<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Graph;

final class Edge
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly string $relationship,
        public readonly array $properties = []
    ) {}
}

