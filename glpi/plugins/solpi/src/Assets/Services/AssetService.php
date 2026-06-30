<?php

declare(strict_types=1);

namespace SOLPI\Assets\Services;

use Ramsey\Uuid\Uuid;
use SOLPI\Assets\Entities\Asset;
use SOLPI\Assets\Repositories\AssetRepository;

final class AssetService
{
    private AssetRepository $repository;

    public function __construct()
    {
        $this->repository = new AssetRepository();
    }

    public function create(
        string $name,
        string $type
    ): Asset {

        $asset = new Asset(
            Uuid::uuid4()->toString(),
            trim($name),
            strtoupper($type)
        );

        $asset->setId(
            $this->repository->create($asset)
        );

        return $asset;
    }

    public function save(
        Asset $asset
    ): Asset {

        if ($asset->id() === null) {

            $asset->setId(
                $this->repository->create($asset)
            );

            return $asset;
        }

        $asset->touch();

        $this->repository->update(
            $asset->id(),
            $asset
        );

        return $asset;
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
