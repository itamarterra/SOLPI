<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;

final class SourceCheckpointRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;
        if (!$DB instanceof DBmysql) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }

        $this->db = $DB;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(string $source, string $adapter, string $name = 'default'): ?array
    {
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_source_checkpoints',
            'WHERE' => [
                'source' => $source,
                'adapter' => $adapter,
                'name' => $name,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * @param array<string,mixed> $metadata
     */
    public function upsert(string $source, string $adapter, string $name, string $lastValue, array $metadata = []): int
    {
        $existing = $this->find($source, $adapter, $name);

        $data = [
            'last_value' => $lastValue,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
        ];

        if (is_array($existing) && isset($existing['id'])) {
            $id = (int)$existing['id'];
            $this->db->update('glpi_plugin_solpi_source_checkpoints', $data, ['id' => $id]);
            return $id;
        }

        $this->db->insert('glpi_plugin_solpi_source_checkpoints', array_merge($data, [
            'source' => $source,
            'adapter' => $adapter,
            'name' => $name,
        ]));

        return (int)$this->db->insertId();
    }

    public function delete(string $source, string $adapter, string $name = 'default'): void
    {
        $this->db->delete('glpi_plugin_solpi_source_checkpoints', [
            'source' => $source,
            'adapter' => $adapter,
            'name' => $name,
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(string $source, ?string $adapter = null, int $limit = 100): array
    {
        $where = ['source' => $source];
        if (is_string($adapter) && $adapter !== '') {
            $where['adapter'] = $adapter;
        }

        $items = [];
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_source_checkpoints',
            'WHERE' => $where,
            'ORDER' => 'updated_at DESC',
            'LIMIT' => max(1, min(500, $limit)),
        ]) as $row) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function hydrate(array $row): array
    {
        $metadata = [];
        if (isset($row['metadata']) && is_string($row['metadata']) && $row['metadata'] !== '') {
            $decoded = json_decode($row['metadata'], true);
            if (is_array($decoded)) {
                $metadata = $decoded;
            }
        }

        return [
            'id' => (int)($row['id'] ?? 0),
            'source' => (string)($row['source'] ?? ''),
            'adapter' => (string)($row['adapter'] ?? ''),
            'name' => (string)($row['name'] ?? 'default'),
            'last_value' => (string)($row['last_value'] ?? ''),
            'metadata' => $metadata,
            'updated_at' => (string)($row['updated_at'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
        ];
    }
}
