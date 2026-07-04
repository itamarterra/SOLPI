<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class MemoryEngine
{
    /**
     * @var array<string,KnowledgeEntity>
     */
    private array $memory = [];

    public function remember(
        KnowledgeEntity $entity
    ): void {

        $this->memory[$entity->uuid()] = $entity;

    }

    public function forget(
        string $uuid
    ): void {

        unset($this->memory[$uuid]);

    }

    public function find(
        string $uuid
    ): ?KnowledgeEntity {

        return $this->memory[$uuid] ?? null;

    }

    public function all(): array
    {
        return array_values(
            $this->memory
        );
    }

    public function clear(): void
    {
        $this->memory = [];
    }

    public function count(): int
    {
        return count($this->memory);
    }
}