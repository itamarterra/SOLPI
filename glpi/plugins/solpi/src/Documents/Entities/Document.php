<?php
declare(strict_types=1);

namespace SOLPI\Documents\Entities;

final class Document
{
    public function __construct(
        public readonly int $id,
        public readonly string $filename,
        public readonly string $path,
        public readonly int $size
    ) {}
}

