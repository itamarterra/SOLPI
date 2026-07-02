<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Security;

use DBmysql;
use RuntimeException;

final class IdempotencyService
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

    public function exists(string $source, string $event, string $hash): bool
    {
        foreach ($this->db->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_solpi_webhooks',
            'WHERE' => [
                'source' => $source,
                'event' => $event,
                'status' => ['RECEIVED', 'QUEUED', 'PROCESSED'],
            ],
            'ORDER' => 'id DESC',
            'LIMIT' => 50,
        ]) as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            foreach ($this->db->request([
                'SELECT' => ['payload'],
                'FROM' => 'glpi_plugin_solpi_webhooks',
                'WHERE' => ['id' => $id],
                'LIMIT' => 1,
            ]) as $payloadRow) {
                $payload = (string)($payloadRow['payload'] ?? '');
                if ($payload !== '' && hash('sha256', $payload) === $hash) {
                    return true;
                }
            }
        }

        return false;
    }
}
