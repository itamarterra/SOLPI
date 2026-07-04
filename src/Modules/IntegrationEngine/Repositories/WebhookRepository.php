<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class WebhookRepository
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

    public function alreadyProcessed(string $source, string $idempotencyKey): bool
    {
        foreach ($this->db->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_solpi_webhooks',
            'WHERE' => [
                'source' => $source,
                'event' => $idempotencyKey,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            return isset($row['id']);
        }

        return false;
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function register(string $source, string $idempotencyKey, array $payload, string $status, ?string $error = null): int
    {
        $this->db->insert('glpi_plugin_solpi_webhooks', [
            'source' => $source,
            'event' => $idempotencyKey,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => $status,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'error' => $error,
        ]);

        return (int)$this->db->insertId();
    }
}
