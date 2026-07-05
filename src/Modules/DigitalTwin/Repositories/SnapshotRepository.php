<?php

declare(strict_types=1);

namespace SOLPI\Modules\DigitalTwin\Repositories;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\Modules\DigitalTwin\Entities\Snapshot;

/**
 * Repositório para persistência de Snapshots do Digital Twin.
 */
final class SnapshotRepository
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    public function create(Snapshot $snapshot): int
    {
        $conn = $this->db->getConnection();
        $conn->insert('glpi_plugin_solpi_inframap_snapshots', [
            'name'       => $snapshot->name(),
            'payload'    => json_encode($snapshot->data(), JSON_UNESCAPED_UNICODE),
            'created_at' => $snapshot->createdAt(),
        ]);

        return (int)$conn->getNativeConnection()->insertId();
    }

    public function findLatest(): ?Snapshot
    {
        $row = $this->db->table('glpi_plugin_solpi_inframap_snapshots')
            ->order('created_at DESC')
            ->first();

        if (!$row) return null;

        return new Snapshot(
            (string)$row['name'],
            json_decode((string)$row['payload'], true) ?: [],
            (int)$row['id'],
            (string)$row['created_at']
        );
    }
}
