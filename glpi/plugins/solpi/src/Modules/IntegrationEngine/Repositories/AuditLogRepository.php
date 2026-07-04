<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class AuditLogRepository
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
     * @param array<string,mixed> $context
     */
    public function write(string $module, string $level, string $message, array $context = []): int
    {
        $this->db->insert('glpi_plugin_solpi_logs', [
            'module' => $module,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_id' => isset($_SESSION['glpiID']) ? (int)$_SESSION['glpiID'] : null,
        ]);

        return (int)$this->db->insertId();
    }
}
