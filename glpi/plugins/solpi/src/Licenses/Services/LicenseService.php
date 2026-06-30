<?php

declare(strict_types=1);

namespace SOLPI\Licenses\Services;

use Ramsey\Uuid\Uuid;
use SOLPI\Licenses\Entities\License;
use SOLPI\Licenses\Repositories\LicenseRepository;

final class LicenseService
{
    private LicenseRepository $repository;

    public function __construct()
    {
        $this->repository = new LicenseRepository();
    }

    public function create(
        string $name,
        string $serial
    ): License {

        $license = new License(
            Uuid::uuid4()->toString(),
            trim($name),
            trim($serial)
        );

        $license->setId(
            $this->repository->create($license)
        );

        return $license;
    }

    public function save(
        License $license
    ): License {

        if ($license->id() === null) {

            $license->setId(
                $this->repository->create($license)
            );

            return $license;
        }

        $license->touch();

        $this->repository->update(
            $license->id(),
            $license
        );

        return $license;
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

    public function all(): array
    {
        return $this->repository->all();
    }

    public function count(): int
    {
        return $this->repository->count();
    }
}
