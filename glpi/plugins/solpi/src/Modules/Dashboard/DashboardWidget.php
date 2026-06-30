<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

final class DashboardWidget
{
    public function cards(): array
    {
        return [

            'tickets',

            'alerts',

            'users',

            'zabbix',

            'whatsapp',

            'ai',

            'uptime'

        ];
    }
}