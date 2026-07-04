<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

final class AlertService
{
    public function __construct(
        private readonly EventService $events
    ) {
    }

    public function latest(): array
    {
        return $this->events->latest();
    }
}
