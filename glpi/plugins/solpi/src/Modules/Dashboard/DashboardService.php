<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

final class DashboardService
{
    public function dashboard(): DashboardModel
    {
        $repository = new DashboardRepository();

        return new DashboardModel(

            openTickets: $repository->openTickets(),

            closedTickets: $repository->closedTickets(),

            alerts: $repository->alerts(),

            users: $repository->users(),

            messages: $repository->whatsapp(),

            aiRequests: $repository->ai(),

            zabbixOnline: false,

            whatsappOnline: false,

            aiOnline: false,

            uptime: 100
        );
    }
}