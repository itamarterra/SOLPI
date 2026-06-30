<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Builders;

use Ramsey\Uuid\Uuid;
use SOLPI\Assets\Entities\Asset;
use SOLPI\Companies\Entities\Company;
use SOLPI\Licenses\Entities\License;
use SOLPI\Users\Entities\User;

final class EntityBuilder
{
    public function buildCompany(
        string $name
    ): Company {

        return new Company(
            Uuid::uuid4()->toString(),
            trim($name)
        );
    }

    public function buildUser(
        string $name
    ): User {

        return new User(
            Uuid::uuid4()->toString(),
            trim($name)
        );
    }

    public function buildAsset(
        array $data
    ): Asset {

        $asset = new Asset(
            Uuid::uuid4()->toString(),
            $data['name'] ?? 'SEM NOME',
            $data['type'] ?? 'DESKTOP'
        );

        $asset
            ->setManufacturer($data['manufacturer'] ?? null)
            ->setModel($data['model'] ?? null)
            ->setSerial($data['serial'] ?? null)
            ->setAssetTag($data['asset_tag'] ?? null)
            ->setPurchaseDate($data['purchase_date'] ?? null)
            ->setWarrantyDate($data['warranty_date'] ?? null)
            ->setLocation($data['location'] ?? null);

        return $asset;
    }

    public function buildLicense(
        array $data
    ): License {

        $license = new License(
            Uuid::uuid4()->toString(),
            $data['name'] ?? '',
            $data['serial'] ?? ''
        );

        $license
            ->setVendor($data['vendor'] ?? null)
            ->setVersion($data['version'] ?? null)
            ->setCategory($data['category'] ?? null)
            ->setPurchaseDate($data['purchase_date'] ?? null)
            ->setExpirationDate($data['expiration_date'] ?? null);

        if (isset($data['value'])) {
            $license->setValue((float)$data['value']);
        }

        return $license;
    }
}
