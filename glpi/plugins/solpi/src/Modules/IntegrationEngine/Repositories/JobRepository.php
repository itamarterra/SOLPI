<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;
use SOLPI\Modules\IntegrationEngine\Queue\QueueConsumerInterface;
use SOLPI\Modules\IntegrationEngine\Queue\QueueProducerInterface;

final class JobRepository implements QueueProducerInterface, QueueConsumerInterface
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

    public function enqueue(string $name, string $handler, array $payload, int $maxAttempts = 3): int
    {
        $this->db->insert('glpi_plugin_solpi_jobs', [
            'name' => $name,
            'handler' => $handler,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'PENDING',
            'attempts' => 0,
            'max_attempts' => max(1, $maxAttempts),
            'scheduled_at' => date('Y-m-d H:i:s'),
        ]);

        return (int)$this->db->insertId();
    }

    /**
     * @param array<int,array{name:string,handler:string,payload:array<string,mixed>,max_attempts?:int}> $jobs
     * @return array<int,int>
     */
    public function enqueueBatch(array $jobs): array
    {
        if ($jobs === []) {
            return [];
        }

        $this->db->query('START TRANSACTION');
        $ids = [];

        try {
            foreach ($jobs as $job) {
                $this->db->insert('glpi_plugin_solpi_jobs', [
                    'name' => (string)$job['name'],
                    'handler' => (string)$job['handler'],
                    'payload' => json_encode($job['payload'], JSON_UNESCAPED_UNICODE),
                    'status' => 'PENDING',
                    'attempts' => 0,
                    'max_attempts' => max(1, (int)($job['max_attempts'] ?? 5)),
                    'scheduled_at' => date('Y-m-d H:i:s'),
                ]);

                $ids[] = (int)$this->db->insertId();
            }

            $this->db->query('COMMIT');
        } catch (
            \Throwable $e
        ) {
            $this->db->query('ROLLBACK');
            throw $e;
        }

        return $ids;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array
    {
        $rows = [];

        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_jobs',
            'WHERE' => ['status' => 'PENDING'],
            'ORDER' => 'scheduled_at ASC',
            'LIMIT' => max(1, min(200, $limit)),
        ]) as $row) {
            $rows[] = $this->hydrate($row);
        }

        return $rows;
    }

    public function markRunning(int $jobId): void
    {
        $this->db->query(
            'UPDATE glpi_plugin_solpi_jobs '
            . 'SET status = "RUNNING", started_at = NOW(), attempts = attempts + 1 '
            . 'WHERE id = ' . $jobId
        );
    }

    public function markSuccess(int $jobId): void
    {
        $this->db->update('glpi_plugin_solpi_jobs', [
            'status' => 'DONE',
            'finished_at' => date('Y-m-d H:i:s'),
            'error' => null,
        ], [
            'id' => $jobId,
        ]);
    }

    public function markFailed(int $jobId, string $error): void
    {
        $safeError = mb_substr($error, 0, 6000);

        $this->db->query(
            'UPDATE glpi_plugin_solpi_jobs '
            . 'SET status = CASE WHEN attempts >= max_attempts THEN "DEAD" ELSE "PENDING" END, '
            . 'error = "' . $this->db->escape($safeError) . '", '
            . 'finished_at = NOW() '
            . 'WHERE id = ' . $jobId
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 30): array
    {
        $rows = [];

        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_jobs',
            'ORDER' => 'id DESC',
            'LIMIT' => max(1, min(200, $limit)),
        ]) as $row) {
            $rows[] = $this->hydrate($row);
        }

        return $rows;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function hydrate(array $row): array
    {
        $payload = [];
        if (isset($row['payload']) && is_string($row['payload']) && $row['payload'] !== '') {
            $decoded = json_decode($row['payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        return [
            'id' => (int)($row['id'] ?? 0),
            'name' => (string)($row['name'] ?? ''),
            'handler' => (string)($row['handler'] ?? ''),
            'payload' => $payload,
            'status' => (string)($row['status'] ?? 'PENDING'),
            'attempts' => (int)($row['attempts'] ?? 0),
            'max_attempts' => (int)($row['max_attempts'] ?? 0),
            'error' => $row['error'] ?? null,
            'scheduled_at' => (string)($row['scheduled_at'] ?? ''),
            'started_at' => $row['started_at'] ?? null,
            'finished_at' => $row['finished_at'] ?? null,
        ];
    }
}
