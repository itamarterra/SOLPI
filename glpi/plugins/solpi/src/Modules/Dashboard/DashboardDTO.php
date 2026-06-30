<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

final class DashboardDTO
{
    public function __construct(

        public readonly int $tickets,

        public readonly int $alerts,

        public readonly int $users,

        public readonly float $uptime

    ) {
    }
}