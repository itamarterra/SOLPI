<?php

declare(strict_types=1);

namespace SOLPI\Licenses\Repositories;

use DBmysql;
use RuntimeException;
use SOLPI\Licenses\Entities\License;

final class LicenseRepository
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

    public function create(License $license): int
    {
        $this->db->insert(
            'glpi_plugin_solpi_licenses',
            [
                'uuid'             => $license->uuid(),
                'name'             => $license->name(),
                'serial'           => $license->serial(),
                'vendor'           => $license->vendor(),
                'version'          => $license->version(),
                'category'         => $license->category(),
                'purchase_date'    => $license->purchaseDate(),
                'expiration_date'  => $license->expirationDate(),
                'value'            => $license->value(),
                'company_id'       => $license->companyId(),
                'asset_id'         => $license->assetId(),
                'active'           => $license->active() ? 1 : 0,
                'metadata'         => json_encode(
                    $license->metadata(),
                    JSON_UNESCAPED_UNICODE
                ),
                'created_at'       => $license->createdAt()->format('Y-m-d H:i:s'),
                'updated_at'       => $license->updatedAt()->format('Y-m-d H:i:s')
            ]
        );

        return (int)$this->db->insertId();
    }

    public function update(int $id, License $license): bool
    {
        return (bool)$this->db->update(
            'glpi_plugin_solpi_licenses',
            [
                'name'            => $license->name(),
                'serial'          => $license->serial(),
                'vendor'          => $license->vendor(),
                'version'         => $license->version(),
                'category'        => $license->category(),
                'purchase_date'   => $license->purchaseDate(),
                'expiration_date' => $license->expirationDate(),
                'value'           => $license->value(),
                'company_id'      => $license->companyId(),
                'asset_id'        => $license->assetId(),
                'active'          => $license->active() ? 1 : 0,
                'metadata'        => json_encode(
                    $license->metadata(),
                    JSON_UNESCAPED_UNICODE
                ),
                'updated_at'      => date('Y-m-d H:i:s')
            ],
            [
                'id' => $id
            ]
        );
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db->delete(
            'glpi_plugin_solpi_licenses',
            [
                'id' => $id
            ]
        );
    }

    public function find(int $id): ?array
    {
        foreach (
            $this->db->request([
                'FROM' => 'glpi_plugin_solpi_licenses',
                'WHERE' => [
                    'id' => $id
                ]
            ])
            as $row
        ) {
            return $row;
        }

        return null;
    }

    public function all(): array
    {
        return iterator_to_array(
            $this->db->request([
                'FROM' => 'glpi_plugin_solpi_licenses',
                'ORDER' => 'name ASC'
            ])
        );
    }

    public function count(): int
    {
        return count($this->all());
    }
}
