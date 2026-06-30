<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class KnowledgeGraph
{
    /**
     * @var array<string,KnowledgeEntity>
     */
    private array $entities = [];

    /**
     * @var KnowledgeRelationship[]
     */
    private array $relationships = [];

    public function addEntity(
        KnowledgeEntity $entity
    ): void {

        $this->entities[$entity->uuid()] = $entity;

    }

    public function addRelationship(
        KnowledgeRelationship $relationship
    ): void {

        $this->relationships[] = $relationship;

    }

    public function entities(): array
    {
        return array_values(
            $this->entities
        );
    }

    public function relationships(): array
    {
        return $this->relationships;
    }

    public function export(): array
    {
        return [

            'entities' => array_map(

                fn(KnowledgeEntity $entity) => $entity->toArray(),

                $this->entities()

            ),

            'relationships' => array_map(

                fn(KnowledgeRelationship $relationship)
                    => $relationship->toArray(),

                $this->relationships()

            )

        ];
    }
}