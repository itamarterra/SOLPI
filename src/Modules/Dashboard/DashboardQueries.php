<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

use DBmysql;

final class DashboardQueries
{
    public function __construct(
        private DBmysql $db
    ) {
    }

    public function totalTickets(): int
    {
        $iterator = $this->db->request([
            'COUNT' => 'c',
            'FROM'  => 'glpi_tickets'
        ]);

        foreach ($iterator as $row) {
            return (int)$row['c'];
        }

        return 0;
    }

    public function totalUsers(): int
    {
        $iterator = $this->db->request([
            'COUNT' => 'c',
            'FROM'  => 'glpi_users'
        ]);

        foreach ($iterator as $row) {
            return (int)$row['c'];
        }

        return 0;
    }

    public function totalAlerts(): int
    {
        $iterator = $this->db->request([
            'COUNT' => 'c',
            'FROM'  => 'glpi_plugin_solpi_alerts'
        ]);

        foreach ($iterator as $row) {
            return (int)$row['c'];
        }

        return 0;
    }
}