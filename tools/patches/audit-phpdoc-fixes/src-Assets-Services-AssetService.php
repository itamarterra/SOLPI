<?php

// Export of modified file: src/Assets/Services/AssetService.php

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

    /**
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function find(
        int $id
    ): ?array {

        /** @var array<string,mixed>|null $result */
        $result = $this->repository->find($id);

        return $result;

    }

    /**
     * @param string $uuid
     * @return array<string,mixed>|null
     */
    public function findByUUID(
        string $uuid
    ): ?array {

        /** @var array<string,mixed>|null $result */
        $result = $this->repository->findByUUID($uuid);

        return $result;

    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        /** @var array<int,array<string,mixed>> $list */
        $list = $this->repository->all();

        return $list;
    }

    public function count(): int
    {
        return $this->repository->count();
    }
}
