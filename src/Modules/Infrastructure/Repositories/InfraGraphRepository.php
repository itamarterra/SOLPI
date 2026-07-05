<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Repositories;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\Modules\Infrastructure\Entities\InfraNode;
use SOLPI\Modules\Infrastructure\Entities\InfraEdge;
use RuntimeException;

/**
 * Repositório Enterprise para gestão do Infrastructure Graph (Digital Twin).
 */
final class InfraGraphRepository
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Adiciona ou atualiza um nó no mapa de infraestrutura.
     * Implementa lógica de versionamento e detecção de mudanças (Digital Twin).
     */
    public function upsertNode(InfraNode $node): bool
    {
        $conn = $this->db->getConnection();
        $tableName = 'glpi_plugin_solpi_inframap_nodes';

        $data = [
            'uuid'        => $node->uuid(),
            'external_id' => $node->externalId(),
            'class'       => $node->class(),
            'label'       => $node->label(),
            'metadata'    => json_encode($node->metadata(), JSON_UNESCAPED_UNICODE),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        $existing = $this->db->table($tableName)
            ->where(['uuid' => $node->uuid()])
            ->first();

        if ($existing) {
            // Verifica se houve mudança real nos metadados para registrar no histórico
            if ($existing['metadata'] !== $data['metadata']) {
                $this->logChange($node->uuid(), 'NODE_METADATA_CHANGE', $existing['metadata'], $data['metadata']);
            }
            return (bool)$conn->update($tableName, $data, ['uuid' => $node->uuid()]);
        }

        $data['created_at'] = $node->createdAt();
        return (bool)$conn->insert($tableName, $data);
    }

    /**
     * Registra um relacionamento entre entidades com peso de confiança.
     */
    public function saveEdge(InfraEdge $edge): bool
    {
        $conn = $this->db->getConnection();
        $tableName = 'glpi_plugin_solpi_inframap_edges';

        $data = [
            'source_uuid'     => $edge->sourceUuid(),
            'target_uuid'     => $edge->targetUuid(),
            'relation_type'   => $edge->relationType(),
            'confidence'      => $edge->confidence(),
            'source_protocol' => $edge->sourceProtocol(),
            'metadata'        => json_encode($edge->metadata(), JSON_UNESCAPED_UNICODE),
        ];

        // Chave única composta para evitar arestas duplicadas do mesmo tipo entre os mesmos nós
        $existing = $this->db->table($tableName)
            ->where([
                'source_uuid'   => $edge->sourceUuid(),
                'target_uuid'   => $edge->targetUuid(),
                'relation_type' => $edge->relationType()
            ])
            ->first();

        if ($existing) {
            return (bool)$conn->update($tableName, $data, ['id' => $existing['id']]);
        }

        $data['created_at'] = $edge->createdAt();
        return (bool)$conn->insert($tableName, $data);
    }

    /**
     * Registra mudanças históricas para o Digital Twin.
     */
    private function logChange(string $targetUuid, string $changeType, string $oldValue, string $newValue): void
    {
        $conn = $this->db->getConnection();
        $conn->insert('glpi_plugin_solpi_inframap_history', [
            'target_uuid' => $targetUuid,
            'change_type' => $changeType,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Busca um nó por UUID.
     */
    public function findNode(string $uuid): ?InfraNode
    {
        $row = $this->db->table('glpi_plugin_solpi_inframap_nodes')
            ->where(['uuid' => $uuid])
            ->first();

        if (!$row) return null;

        return new InfraNode(
            (string)$row['uuid'],
            (string)$row['class'],
            (string)$row['label'],
            (string)$row['external_id'],
            json_decode((string)$row['metadata'], true) ?: [],
            (string)$row['created_at'],
            (string)$row['updated_at']
        );
    }
}
