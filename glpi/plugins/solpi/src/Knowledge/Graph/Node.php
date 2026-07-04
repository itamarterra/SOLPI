<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Graph;

final class Node
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly array $properties = []
    ) {}
}

