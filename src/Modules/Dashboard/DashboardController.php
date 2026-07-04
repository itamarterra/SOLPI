<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

use SOLPI\Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $service = new DashboardService();

        $dashboard = $service->dashboard();

        $this->render(
            'dashboard',
            [
                'dashboard' => $dashboard
            ]
        );
    }
}