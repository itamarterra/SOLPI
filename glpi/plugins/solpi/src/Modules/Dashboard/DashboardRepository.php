<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

use DBmysql;

final class DashboardRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        $this->db = $DB;
    }

    public function count(string $table, array $where = []): int
    {
        $iterator = $this->db->request([
            'COUNT' => 'c',
            'FROM'  => $table,
            'WHERE' => $where
        ]);

        foreach ($iterator as $row) {
            return (int)$row['c'];
        }

        return 0;
    }

    public function openTickets(): int
    {
        return $this->count(
            'glpi_tickets',
            [
                'is_deleted' => 0,
                'status' => [1,2,3,4]
            ]
        );
    }

    public function closedTickets(): int
    {
        return $this->count(
            'glpi_tickets',
            [
                'is_deleted' => 0,
                'status' => [5,6]
            ]
        );
    }

    public function users(): int
    {
        return $this->count('glpi_users');
    }

    public function alerts(): int
    {
        return $this->count('glpi_plugin_solpi_alerts');
    }

    public function whatsapp(): int
    {
        return $this->count('glpi_plugin_solpi_whatsapp');
    }

    public function ai(): int
    {
        return $this->count('glpi_plugin_solpi_ai');
    }
}