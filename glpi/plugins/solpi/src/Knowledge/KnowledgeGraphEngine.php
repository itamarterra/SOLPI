<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class KnowledgeGraphEngine
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository = new KnowledgeRepository();
    }

    public function addNode(
        string $type,
        string $uuid,
        array $payload
    ): void {

        $this->repository->storeEntity(

            $type,

            $uuid,

            $payload

        );

    }

    public function addRelationship(
        string $source,
        string $target,
        string $relation,
        float $weight = 1.0
    ): void {

        $this->repository->storeRelationship(

            $source,

            $target,

            $relation,

            $weight

        );

    }

    public function entities(): array
    {
        return $this->repository->entities();
    }

    public function relationships(): array
    {
        return $this->repository->relationships();
    }
}
