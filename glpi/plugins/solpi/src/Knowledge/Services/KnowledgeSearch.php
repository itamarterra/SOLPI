<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class KnowledgeSearch
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository = new KnowledgeRepository();
    }

    public function search(
        string $term
    ): array {

        $result = [];

        $term = strtoupper($term);

        foreach ($this->repository->entities() as $entity) {

            if (
                str_contains(
                    strtoupper($entity['payload']),
                    $term
                )
            ) {

                $result[] = $entity;

            }

        }

        return $result;

    }
}
