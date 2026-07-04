<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class KnowledgeService
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository = new KnowledgeRepository();
    }

    public function register(
        string $type,
        string $uuid,
        array $payload
    ): int {

        return $this->repository->saveEntity(
            $type,
            $uuid,
            $payload
        );
    }

    public function entities(): array
    {
        return $this->repository->entities();
    }
}
