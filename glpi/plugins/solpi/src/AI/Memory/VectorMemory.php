<?php

declare(strict_types=1);

namespace SOLPI\AI\Memory;

final class VectorMemory
{
    /**
     * @var array<string,array{embedding:array<int,float>,payload:array<string,mixed>}>
     */
    private array $documents = [];

    /**
     * @param array<int,float> $embedding
     * @param array<string,mixed> $payload
     */
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

    /**
     * @return array<string,array{embedding:array<int,float>,payload:array<string,mixed>}>
     */
    public function all(): array
    {
        return $this->documents;
    }

    public function clear(): void
    {
        $this->documents = [];
    }
}
