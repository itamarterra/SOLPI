<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class IdentityMapRepository
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

    /**
     * @return array<int,array<string,mixed>>
     */
    public function findByKeys(string $entityType, array $keys): array
    {
        $candidates = [];

        foreach ($keys as $key) {
            $type = (string)($key['type'] ?? '');
            $value = (string)($key['value'] ?? '');

            if ($type === '' || $value === '') {
                continue;
            }

            foreach ($this->db->request([
                'FROM' => 'glpi_plugin_solpi_identity_map',
                'WHERE' => [
                    'entity_type' => $entityType,
                    'key_type' => $type,
                    'key_value' => $value,
                ],
            ]) as $row) {
                $candidates[] = $row;
            }
        }

        return $candidates;
    }

    public function upsertKey(
        string $entityType,
        string $canonicalId,
        string $keyType,
        string $keyValue,
        float $confidence,
        ?string $source,
        ?string $rawHash,
        array $metadata = []
    ): void {
        $existing = null;

        foreach ($this->db->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_solpi_identity_map',
            'WHERE' => [
                'entity_type' => $entityType,
                'key_type' => $keyType,
                'key_value' => $keyValue,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            $existing = (int)$row['id'];
        }

        $payload = [
            'canonical_id' => $canonicalId,
            'confidence' => $confidence,
            'source' => $source,
            'raw_hash' => $rawHash,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
        ];

        if ($existing !== null) {
            $this->db->update('glpi_plugin_solpi_identity_map', $payload, ['id' => $existing]);
            return;
        }

        $this->db->insert('glpi_plugin_solpi_identity_map', array_merge($payload, [
            'entity_type' => $entityType,
            'key_type' => $keyType,
            'key_value' => $keyValue,
        ]));
    }
}
