<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class IntegrationJobRepository
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
     */
    public function create(string $name, string $handler, array $payload, int $maxAttempts = 3): int
    {
        $this->db->insert('glpi_plugin_solpi_jobs', [
            'name' => $name,
            'handler' => $handler,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'PENDING',
            'attempts' => 0,
            'max_attempts' => $maxAttempts,
            'scheduled_at' => date('Y-m-d H:i:s'),
        ]);

        return (int)$this->db->insertId();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $items = [];

        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_jobs',
            'WHERE' => ['status' => 'PENDING'],
            'ORDER' => 'scheduled_at ASC',
            'LIMIT' => $limit,
        ]) as $row) {
            $payload = json_decode((string)($row['payload'] ?? '{}'), true);
            $items[] = [
                'id' => (int)($row['id'] ?? 0),
                'name' => (string)($row['name'] ?? ''),
                'handler' => (string)($row['handler'] ?? ''),
                'payload' => is_array($payload) ? $payload : [],
                'attempts' => (int)($row['attempts'] ?? 0),
                'max_attempts' => (int)($row['max_attempts'] ?? 3),
            ];
        }

        return $items;
    }

    public function markRunning(int $jobId): void
    {
        $this->db->update('glpi_plugin_solpi_jobs', [
            'status' => 'RUNNING',
            'started_at' => date('Y-m-d H:i:s'),
        ], [
            'id' => $jobId,
        ]);
    }

    public function markSuccess(int $jobId): void
    {
        $this->db->update('glpi_plugin_solpi_jobs', [
            'status' => 'DONE',
            'finished_at' => date('Y-m-d H:i:s'),
        ], [
            'id' => $jobId,
        ]);
    }

    public function markFailed(int $jobId, string $error, bool $retryable = true): void
    {
        $current = null;
        foreach ($this->db->request([
            'SELECT' => ['attempts', 'max_attempts'],
            'FROM' => 'glpi_plugin_solpi_jobs',
            'WHERE' => ['id' => $jobId],
            'LIMIT' => 1,
        ]) as $row) {
            $current = $row;
        }

        if (!is_array($current)) {
            return;
        }

        $attempts = (int)$current['attempts'] + 1;
        $max = (int)$current['max_attempts'];

        $status = ($retryable && $attempts < $max) ? 'PENDING' : 'FAILED';

        $this->db->update('glpi_plugin_solpi_jobs', [
            'status' => $status,
            'attempts' => $attempts,
            'error' => $error,
            'finished_at' => $status === 'FAILED' ? date('Y-m-d H:i:s') : null,
        ], [
            'id' => $jobId,
        ]);
    }
}
