<?php

declare(strict_types=1);

namespace SOLPI\Repositories;

final class DashboardRepository
{
    public function getDashboardData(): array
    {
        return [

            'openTickets' => 0,

            'closedTickets' => 0,

            'pendingTickets' => 0,

            'resolvedToday' => 0,

            'hostsOnline' => 0,

            'hostsOffline' => 0

        ];
    }
}
