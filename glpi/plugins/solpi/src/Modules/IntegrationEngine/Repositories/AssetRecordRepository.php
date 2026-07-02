<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class AssetRecordRepository
{
    private object $db;

    public function __construct()
    {
        global $DB;
        if (!is_object($DB)) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }
        $this->db = $DB;
    }

    public function findCandidate(array $record): ?array
    {
        $uuid = (string)($record['uuid'] ?? '');
        $serial = trim((string)($record['serial'] ?? ''));
        $assetTag = trim((string)($record['asset_tag'] ?? $record['tag'] ?? ''));
        $name = trim((string)($record['hostname'] ?? $record['name'] ?? ''));

        foreach ([
            $uuid !== '' ? ['uuid' => $uuid] : null,
            $serial !== '' ? ['serial' => $serial] : null,
            $assetTag !== '' ? ['asset_tag' => $assetTag] : null,
            $name !== '' ? ['name' => $name] : null,
        ] as $where) {
            if (!is_array($where)) {
                continue;
            }

            $found = $this->findBy($where);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     */
    public function upsert(array $record): array
    {
        $candidate = $this->findCandidate($record);

        $data = [
            'uuid' => (string)($record['uuid'] ?? hash('sha256', json_encode($record))),
            'name' => (string)($record['hostname'] ?? $record['name'] ?? 'Ativo sem nome'),
            'type' => (string)($record['type'] ?? 'DEVICE'),
            'manufacturer' => $record['manufacturer'] ?? null,
            'model' => $record['model'] ?? null,
            'serial' => $record['serial'] ?? null,
            'asset_tag' => $record['asset_tag'] ?? ($record['tag'] ?? null),
            'company_id' => isset($record['company_id']) ? (int)$record['company_id'] : null,
            'user_id' => isset($record['user_id']) ? (int)$record['user_id'] : null,
            'location' => $record['location'] ?? null,
            'purchase_date' => $record['purchase_date'] ?? null,
            'warranty_date' => $record['warranty_date'] ?? null,
            'active' => 1,
            'metadata' => json_encode($record['metadata'] ?? $record, JSON_UNESCAPED_UNICODE),
        ];

        if ($candidate !== null) {
            $id = (int)$candidate['id'];
            $this->db->update('glpi_plugin_solpi_assets', $data, ['id' => $id]);
            return ['id' => $id, 'action' => 'updated'];
        }

        $this->db->insert('glpi_plugin_solpi_assets', $data);
        return ['id' => (int)$this->db->insertId(), 'action' => 'created'];
    }

    private function findBy(array $where): ?array
    {
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_assets',
            'WHERE' => $where,
            'LIMIT' => 1,
        ]) as $row) {
            return $row;
        }

        return null;
    }
}
