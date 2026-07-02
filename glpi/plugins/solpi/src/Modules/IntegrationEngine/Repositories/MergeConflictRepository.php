<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;

final class MergeConflictRepository
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
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        $this->db->insert('glpi_plugin_solpi_merge_conflicts', [
            'correlation_id' => $data['correlation_id'] ?? null,
            'entity_type' => (string)($data['entity_type'] ?? 'unknown'),
            'canonical_id' => $data['canonical_id'] ?? null,
            'field_path' => (string)($data['field_path'] ?? 'unknown'),
            'current_value' => json_encode($data['current_value'] ?? null, JSON_UNESCAPED_UNICODE),
            'incoming_value' => json_encode($data['incoming_value'] ?? null, JSON_UNESCAPED_UNICODE),
            'decision' => (string)($data['decision'] ?? 'KEPT_CURRENT'),
            'reason' => $data['reason'] ?? null,
        ]);

        return (int)$this->db->insertId();
    }
}
