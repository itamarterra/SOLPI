<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class RelationshipBuilder
{
    /**
     * @param KnowledgeEntity[] $entities
     *
     * @return KnowledgeRelationship[]
     */
    public function build(
        array $entities
    ): array {

        $relationships = [];

        foreach ($entities as $entity) {

            if (!$entity->has('company')) {
                continue;
            }

            $relationships[] = new KnowledgeRelationship(

                $entity->uuid(),

                'BELONGS_TO',

                $entity->get('company')

            );

        }

        return $relationships;
    }
}