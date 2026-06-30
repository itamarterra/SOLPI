<?php

declare(strict_types=1);

namespace SOLPI\Core;

abstract class BaseService
{
    protected BaseRepository $repository;

    public function all(): array
    {
        return $this->repository->all();
    }

    public function find(
        int $id
    ): ?array{

        return $this->repository->find($id);

    }

    public function delete(
        int $id
    ): bool{

        return $this->repository->delete($id);

    }

    public function count(): int
    {
        return $this->repository->count();
    }
}
