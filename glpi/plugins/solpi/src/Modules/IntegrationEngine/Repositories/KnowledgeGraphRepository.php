<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;

final class KnowledgeGraphRepository
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
     * @param array<string,mixed> $properties
     */
    public function upsertNode(string $canonicalId, string $entityType, ?int $entityId, ?string $label, array $properties = []): void
    {
        $existingId = null;

        foreach ($this->db->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_solpi_kg_nodes',
            'WHERE' => [
                'canonical_id' => $canonicalId,
                'entity_type' => $entityType,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            $existingId = (int)$row['id'];
        }

        $data = [
            'entity_id' => $entityId,
            'label' => $label,
            'properties' => json_encode($properties, JSON_UNESCAPED_UNICODE),
        ];

        if ($existingId !== null) {
            $this->db->update('glpi_plugin_solpi_kg_nodes', $data, ['id' => $existingId]);
            return;
        }

        $this->db->insert('glpi_plugin_solpi_kg_nodes', array_merge($data, [
            'canonical_id' => $canonicalId,
            'entity_type' => $entityType,
        ]));
    }

    /**
     * @param array<string,mixed> $properties
     */
    public function upsertEdge(string $sourceCanonicalId, string $targetCanonicalId, string $relation, float $weight = 1.0, array $properties = []): void
    {
        $existingId = null;

        foreach ($this->db->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_solpi_kg_edges',
            'WHERE' => [
                'source_canonical_id' => $sourceCanonicalId,
                'target_canonical_id' => $targetCanonicalId,
                'relation' => $relation,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            $existingId = (int)$row['id'];
        }

        $data = [
            'weight' => $weight,
            'properties' => json_encode($properties, JSON_UNESCAPED_UNICODE),
        ];

        if ($existingId !== null) {
            $this->db->update('glpi_plugin_solpi_kg_edges', $data, ['id' => $existingId]);
            return;
        }

        $this->db->insert('glpi_plugin_solpi_kg_edges', array_merge($data, [
            'source_canonical_id' => $sourceCanonicalId,
            'target_canonical_id' => $targetCanonicalId,
            'relation' => $relation,
        ]));
    }
}
