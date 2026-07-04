<?php
declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

use DBmysql;
use RuntimeException;

final class ZabbixRepository
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

    public function create(array $alert): int
    {
        $this->db->insert('glpi_plugin_solpi_alerts', [
            'eventid' => isset($alert['eventid']) ? (int)$alert['eventid'] : null,
            'host' => (string)($alert['host'] ?? 'unknown'),
            'trigger_name' => (string)($alert['trigger_name'] ?? 'Zabbix alert'),
            'severity' => (string)($alert['severity'] ?? 'warning'),
            'status' => (string)($alert['status'] ?? 'OPEN'),
            'raw_data' => json_encode($alert['raw_data'] ?? $alert, JSON_UNESCAPED_UNICODE),
        ]);

        return (int)$this->db->insertId();
    }

    /**
     * @return array<string,int>
     */
    public function summary(): array
    {
        $summary = [
            'open' => 0,
            'resolved' => 0,
            'total' => 0,
        ];

        foreach ($this->db->request([
            'COUNT' => 'c',
            'FROM' => 'glpi_plugin_solpi_alerts',
            'WHERE' => ['status' => 'OPEN'],
        ]) as $row) {
            $summary['open'] = (int)$row['c'];
        }

        foreach ($this->db->request([
            'COUNT' => 'c',
            'FROM' => 'glpi_plugin_solpi_alerts',
            'WHERE' => ['status' => ['RESOLVED', 'CLOSED']],
        ]) as $row) {
            $summary['resolved'] = (int)$row['c'];
        }

        foreach ($this->db->request([
            'COUNT' => 'c',
            'FROM' => 'glpi_plugin_solpi_alerts',
        ]) as $row) {
            $summary['total'] = (int)$row['c'];
        }

        return $summary;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $rows = [];

        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_alerts',
            'ORDER' => 'id DESC',
            'LIMIT' => $limit,
        ]) as $row) {
            $rows[] = [
                'id' => (int)($row['id'] ?? 0),
                'eventid' => isset($row['eventid']) ? (int)$row['eventid'] : null,
                'host' => (string)($row['host'] ?? 'unknown'),
                'trigger_name' => (string)($row['trigger_name'] ?? ''),
                'severity' => (string)($row['severity'] ?? 'warning'),
                'status' => (string)($row['status'] ?? 'OPEN'),
                'ticket_id' => isset($row['ticket_id']) ? (int)$row['ticket_id'] : null,
                'created_at' => (string)($row['created_at'] ?? ''),
                'updated_at' => (string)($row['updated_at'] ?? ''),
            ];
        }

        return $rows;
    }

    public function acknowledge(int $alertId): void
    {
        $this->db->update('glpi_plugin_solpi_alerts', [
            'status' => 'ACKNOWLEDGED',
        ], [
            'id' => $alertId,
        ]);
    }
}

