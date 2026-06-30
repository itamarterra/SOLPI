<?php

declare(strict_types=1);

namespace SOLPI\Services;

use SOLPI\Models\DashboardModel;
use SOLPI\Repositories\DashboardRepository;

final class DashboardService
{
    private DashboardRepository $repository;

    public function __construct()
    {
        $this->repository = new DashboardRepository();
    }

    public function load(): DashboardModel
    {
        $data = $this->repository->getDashboardData();

        $dashboard = new DashboardModel();

        $dashboard->setOpenTickets($data['openTickets']);
        $dashboard->setClosedTickets($data['closedTickets']);
        $dashboard->setPendingTickets($data['pendingTickets']);
        $dashboard->setResolvedToday($data['resolvedToday']);
        $dashboard->setHostsOnline($data['hostsOnline']);
        $dashboard->setHostsOffline($data['hostsOffline']);

        return $dashboard;
    }
}
