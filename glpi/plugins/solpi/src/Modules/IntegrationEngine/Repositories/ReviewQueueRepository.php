<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class ReviewQueueRepository
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
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $resolution
     * @param array<int,array<string,mixed>> $conflicts
     */
    public function enqueue(string $entityType, float $confidence, array $payload, array $resolution, array $conflicts, ?string $correlationId = null): int
    {
        $this->db->insert('glpi_plugin_solpi_review_queue', [
            'correlation_id' => $correlationId,
            'entity_type' => $entityType,
            'canonical_id' => $resolution['canonical_id'] ?? null,
            'confidence' => $confidence,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'resolution' => json_encode($resolution, JSON_UNESCAPED_UNICODE),
            'conflicts' => json_encode($conflicts, JSON_UNESCAPED_UNICODE),
            'status' => 'PENDING',
        ]);

        return (int)$this->db->insertId();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array
    {
        $items = [];
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_review_queue',
            'WHERE' => ['status' => 'PENDING'],
            'ORDER' => 'id ASC',
            'LIMIT' => max(1, min(200, $limit)),
        ]) as $row) {
            $items[] = $row;
        }

        return $items;
    }

    public function markReviewed(int $id, string $status, ?string $reason = null): void
    {
        $this->db->update('glpi_plugin_solpi_review_queue', [
            'status' => $status,
            'decision_reason' => $reason,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewer_id' => isset($_SESSION['glpiID']) ? (int)$_SESSION['glpiID'] : null,
        ], [
            'id' => $id,
        ]);
    }
}
