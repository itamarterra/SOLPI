<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class KnowledgeStatistics
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository = new KnowledgeRepository();
    }

    public function totalEntities(): int
    {
        return count(
            $this->repository->entities()
        );
    }

    public function byType(): array
    {
        $statistics = [];

        foreach ($this->repository->entities() as $entity) {

            $type = $entity['entity_type'];

            if (!isset($statistics[$type])) {
                $statistics[$type] = 0;
            }

            $statistics[$type]++;

        }

        ksort($statistics);

        return $statistics;
    }
}
