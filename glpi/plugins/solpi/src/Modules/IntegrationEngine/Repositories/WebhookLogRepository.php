<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class WebhookLogRepository
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
    public function record(string $source, string $event, array $payload, string $status = 'RECEIVED', ?string $error = null): int
    {
        $this->db->insert('glpi_plugin_solpi_webhooks', [
            'source' => $source,
            'event' => $event,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => $status,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'error' => $error,
        ]);

        return (int)$this->db->insertId();
    }
}
