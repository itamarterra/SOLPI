<?php

declare(strict_types=1);

namespace SOLPI\Models;

final class DashboardModel
{
    private int $openTickets = 0;

    private int $closedTickets = 0;

    private int $pendingTickets = 0;

    private int $resolvedToday = 0;

    private int $hostsOnline = 0;

    private int $hostsOffline = 0;

    public function setOpenTickets(int $v): void
    {
        $this->openTickets = $v;
    }

    public function setClosedTickets(int $v): void
    {
        $this->closedTickets = $v;
    }

    public function setPendingTickets(int $v): void
    {
        $this->pendingTickets = $v;
    }

    public function setResolvedToday(int $v): void
    {
        $this->resolvedToday = $v;
    }

    public function setHostsOnline(int $v): void
    {
        $this->hostsOnline = $v;
    }

    public function setHostsOffline(int $v): void
    {
        $this->hostsOffline = $v;
    }

    public function toArray(): array
    {
        return [
            'open_tickets' => $this->openTickets,
            'closed_tickets' => $this->closedTickets,
            'pending_tickets' => $this->pendingTickets,
            'resolved_today' => $this->resolvedToday,
            'hosts_online' => $this->hostsOnline,
            'hosts_offline' => $this->hostsOffline,
        ];
    }
}
