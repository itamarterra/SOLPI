<?php

declare(strict_types=1);

namespace SOLPI\Users\Services;

use Ramsey\Uuid\Uuid;
use SOLPI\Users\Entities\User;
use SOLPI\Users\Repositories\UserRepository;

final class UserService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function create(
        string $name
    ): User {

        $user = new User(
            Uuid::uuid4()->toString(),
            trim($name)
        );

        $user->setId(
            $this->repository->create($user)
        );

        return $user;
    }

    public function save(
        User $user
    ): User {

        if ($user->id() === null) {

            $user->setId(
                $this->repository->create($user)
            );

            return $user;
        }

        $user->touch();

        $this->repository->update(
            $user->id(),
            $user
        );

        return $user;
    }

    public function remove(
        int $id
    ): bool {

        return $this->repository->delete($id);

    }

    public function find(
        int $id
    ): ?array {

        return $this->repository->find($id);

    }

    public function findByUUID(
        string $uuid
    ): ?array {

        return $this->repository->findByUUID($uuid);

    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function count(): int
    {
        return $this->repository->count();
    }
}
