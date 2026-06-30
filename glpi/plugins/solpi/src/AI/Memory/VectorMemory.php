<?php

declare(strict_types=1);

namespace SOLPI\AI\Memory;

final class VectorMemory
{
    private array $documents = [];

    public function add(
        string $id,
        array $embedding,
        array $payload
    ): void {

        $this->documents[$id] = [

            'embedding' => $embedding,

            'payload' => $payload

        ];

    }

    public function all(): array
    {
        return $this->documents;
    }

    public function clear(): void
    {
        $this->documents = [];
    }
}
