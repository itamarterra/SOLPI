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

    public function setOpenTickets(int $value): void
    {
        $this->openTickets = $value;
    }

    public function getOpenTickets(): int
    {
        return $this->openTickets;
    }

    public function setClosedTickets(int $value): void
    {
        $this->closedTickets = $value;
    }

    public function getClosedTickets(): int
    {
        return $this->closedTickets;
    }

    public function setPendingTickets(int $value): void
    {
        $this->pendingTickets = $value;
    }

    public function getPendingTickets(): int
    {
        return $this->pendingTickets;
    }

    public function setResolvedToday(int $value): void
    {
        $this->resolvedToday = $value;
    }

    public function getResolvedToday(): int
    {
        return $this->resolvedToday;
    }

    public function setHostsOnline(int $value): void
    {
        $this->hostsOnline = $value;
    }

    public function getHostsOnline(): int
    {
        return $this->hostsOnline;
    }

    public function setHostsOffline(int $value): void
    {
        $this->hostsOffline = $value;
    }

    public function getHostsOffline(): int
    {
        return $this->hostsOffline;
    }

    public function toArray(): array
    {
        return [
            'openTickets'   => $this->openTickets,
            'closedTickets' => $this->closedTickets,
            'pendingTickets'=> $this->pendingTickets,
            'resolvedToday' => $this->resolvedToday,
            'hostsOnline'   => $this->hostsOnline,
            'hostsOffline'  => $this->hostsOffline
        ];
    }
}
