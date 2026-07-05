<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Repositories;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\Core\Database\QueryBuilder;
use RuntimeException;

/**
 * Repositório para persistência do Incident Graph
 */
final class GraphRepository
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Adiciona ou atualiza um nó no Grafo de Conhecimento
     */
    public function upsertNode(string $canonicalId, string $type, ?string $label = null, array $properties = []): bool
    {
        $conn = $this->db->getConnection();

        $data = [
            'canonical_id' => $canonicalId,
            'entity_type'  => $type,
            'label'        => $label ?? $canonicalId,
            'properties'   => json_encode($properties, JSON_UNESCAPED_UNICODE),
        ];

        // Tenta localizar existente
        $existing = $this->db->table('glpi_plugin_solpi_kg_nodes')
            ->where(['canonical_id' => $canonicalId, 'entity_type' => $type])
            ->first();

        if ($existing) {
            return $conn->update('glpi_plugin_solpi_kg_nodes', $data, ['id' => $existing['id']]);
        }

        return (bool)$conn->insert('glpi_plugin_solpi_kg_nodes', $data);
    }

    /**
     * Cria uma aresta (relacionamento) entre dois nós
     */
    public function addEdge(string $sourceId, string $targetId, string $relation, float $weight = 1.0, array $properties = []): bool
    {
        $conn = $this->db->getConnection();

        $data = [
            'source_canonical_id' => $sourceId,
            'target_canonical_id' => $targetId,
            'relation'            => $relation,
            'weight'              => $weight,
            'properties'          => json_encode($properties, JSON_UNESCAPED_UNICODE),
        ];

        return (bool)$conn->insert('glpi_plugin_solpi_kg_edges', $data);
    }

    /**
     * Busca vizinhos de um nó (conexões diretas)
     */
    public function getNeighbors(string $canonicalId): array
    {
        return iterator_to_array($this->db->table('glpi_plugin_solpi_kg_edges')
            ->where(['source_canonical_id' => $canonicalId])
            ->get());
    }

    /**
     * Busca a origem de um relacionamento (quem afeta o nó atual)
     */
    public function getInboundEdges(string $canonicalId): array
    {
        return iterator_to_array($this->db->table('glpi_plugin_solpi_kg_edges')
            ->where(['target_canonical_id' => $canonicalId])
            ->get());
    }
}
