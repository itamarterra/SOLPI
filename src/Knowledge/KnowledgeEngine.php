<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use SOLPI\Core\Logger;

final class KnowledgeEngine
{
    /**
     * @var array<string,KnowledgeEntity>
     */
    private array $entities = [];

    /**
     * @var KnowledgeRelationship[]
     */
    private array $relationships = [];

    public function __construct(
        private Logger $logger
    ) {
    }

    public function addEntity(
        KnowledgeEntity $entity
    ): void {

        $this->entities[$entity->uuid()] = $entity;

        $this->logger->info(
            'Entidade registrada.',
            [
                'uuid' => $entity->uuid(),
                'type' => $entity->type(),
                'name' => $entity->name()
            ]
        );
    }

    public function entity(
        string $uuid
    ): ?KnowledgeEntity {

        return $this->entities[$uuid] ?? null;

    }

    public function entities(): array
    {
        return array_values(
            $this->entities
        );
    }

    public function addRelationship(
        KnowledgeRelationship $relationship
    ): void {

        $this->relationships[] = $relationship;

    }

    public function relationships(): array
    {
        return $this->relationships;
    }

    public function clear(): void
    {
        $this->entities = [];
        $this->relationships = [];
    }

    public function count(): int
    {
        return count(
            $this->entities
        );
    }
}