<?php

declare(strict_types=1);

namespace SOLPI\Assets\Repositories;

use DBmysql;
use RuntimeException;
use SOLPI\Assets\Entities\Asset;

final class AssetRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException(
                'Conexão com o banco do GLPI não encontrada.'
            );
        }

        $this->db = $DB;
    }

    public function create(Asset $asset): int
    {
        $this->db->insert(
            'glpi_plugin_solpi_assets',
            [
                'uuid'            => $asset->uuid(),
                'name'            => $asset->name(),
                'type'            => $asset->type(),
                'manufacturer'    => $asset->manufacturer(),
                'model'           => $asset->model(),
                'serial'          => $asset->serial(),
                'asset_tag'       => $asset->assetTag(),
                'company_id'      => $asset->companyId(),
                'user_id'         => $asset->userId(),
                'location'        => $asset->location(),
                'purchase_date'   => $asset->purchaseDate(),
                'warranty_date'   => $asset->warrantyDate(),
                'active'          => $asset->active() ? 1 : 0,
                'metadata'        => json_encode(
                    $asset->metadata(),
                    JSON_UNESCAPED_UNICODE
                ),
                'created_at'      => $asset->createdAt()->format('Y-m-d H:i:s'),
                'updated_at'      => $asset->updatedAt()->format('Y-m-d H:i:s')
            ]
        );

        return (int)$this->db->insertId();
    }

    public function update(
        int $id,
        Asset $asset
    ): bool {

        return (bool)$this->db->update(
            'glpi_plugin_solpi_assets',
            [
                'name'          => $asset->name(),
                'type'          => $asset->type(),
                'manufacturer'  => $asset->manufacturer(),
                'model'         => $asset->model(),
                'serial'        => $asset->serial(),
                'asset_tag'     => $asset->assetTag(),
                'company_id'    => $asset->companyId(),
                'user_id'       => $asset->userId(),
                'location'      => $asset->location(),
                'purchase_date' => $asset->purchaseDate(),
                'warranty_date' => $asset->warrantyDate(),
                'active'        => $asset->active() ? 1 : 0,
                'metadata'      => json_encode(
                    $asset->metadata(),
                    JSON_UNESCAPED_UNICODE
                ),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'id' => $id
            ]
        );
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db->delete(
            'glpi_plugin_solpi_assets',
            [
                'id' => $id
            ]
        );
    }

    public function find(int $id): ?array
    {
        $iterator = $this->db->request([
            'FROM' => 'glpi_plugin_solpi_assets',
            'WHERE' => [
                'id' => $id
            ]
        ]);

        foreach ($iterator as $row) {
            return $row;
        }

        return null;
    }

    public function findByUUID(string $uuid): ?array
    {
        $iterator = $this->db->request([
            'FROM' => 'glpi_plugin_solpi_assets',
            'WHERE' => [
                'uuid' => $uuid
            ]
        ]);

        foreach ($iterator as $row) {
            return $row;
        }

        return null;
    }

    public function all(): array
    {
        return iterator_to_array(
            $this->db->request([
                'FROM' => 'glpi_plugin_solpi_assets',
                'ORDER' => 'name ASC'
            ])
        );
    }

    public function count(): int
    {
        return count($this->all());
    }
}
