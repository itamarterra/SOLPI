<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

final class DashboardModel
{
    public function __construct(
        public int $openTickets = 0,
        public int $closedTickets = 0,
        public int $alerts = 0,
        public int $users = 0,
        public int $messages = 0,
        public int $aiRequests = 0,
        public bool $zabbixOnline = false,
        public bool $whatsappOnline = false,
        public bool $aiOnline = false,
        public float $uptime = 100.0
    ) {
    }
}