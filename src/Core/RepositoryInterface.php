<?php

declare(strict_types=1);

namespace SOLPI\Core;

interface RepositoryInterface
{
    public function all(): array;

    public function find(
        int $id
    ): ?array;

    public function delete(
        int $id
    ): bool;

    public function count(): int;
}
