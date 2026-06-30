<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

use DBmysql;

final class DashboardStatistics
{
    private DashboardQueries $queries;

    public function __construct(DBmysql $db)
    {
        $this->queries = new DashboardQueries($db);
    }

    public function load(): array
    {
        return [

            'tickets' => $this->queries->totalTickets(),

            'users' => $this->queries->totalUsers(),

            'alerts' => $this->queries->totalAlerts(),

            'uptime' => 100

        ];
    }
}