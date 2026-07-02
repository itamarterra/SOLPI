<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;

final class DeadLetterRepository
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
     * @param array<string,mixed> $job
     */
    public function create(array $job, string $error): int
    {
        $this->db->insert('glpi_plugin_solpi_dead_letter', [
            'job_id' => (int)($job['id'] ?? 0),
            'name' => (string)($job['name'] ?? 'unknown'),
            'handler' => (string)($job['handler'] ?? 'unknown'),
            'payload' => json_encode($job['payload'] ?? [], JSON_UNESCAPED_UNICODE),
            'error' => $error,
            'attempts' => (int)($job['attempts'] ?? 0) + 1,
            'status' => 'DEAD',
        ]);

        return (int)$this->db->insertId();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(int $limit = 50): array
    {
        $items = [];
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_dead_letter',
            'ORDER' => 'id DESC',
            'LIMIT' => max(1, min(200, $limit)),
        ]) as $row) {
            $items[] = $row;
        }

        return $items;
    }

    public function find(int $id): ?array
    {
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_dead_letter',
            'WHERE' => ['id' => $id],
            'LIMIT' => 1,
        ]) as $row) {
            return $row;
        }

        return null;
    }

    public function markReplayed(int $id): void
    {
        $this->db->update('glpi_plugin_solpi_dead_letter', [
            'status' => 'REPLAYED',
            'replayed_at' => date('Y-m-d H:i:s'),
        ], [
            'id' => $id,
        ]);
    }
}
