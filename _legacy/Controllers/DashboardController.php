<?php

declare(strict_types=1);

namespace SOLPI\Controllers;

use SOLPI\Services\DashboardService;

final class DashboardController
{
    private DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function index(): array
    {
        return $this->service
            ->load()
            ->toArray();
    }
}
