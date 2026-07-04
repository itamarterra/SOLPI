<?php

declare(strict_types=1);

use SOLPI\Modules\Dashboard\DashboardService;

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

require_once dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

$service = new DashboardService();

echo json_encode($service->dashboard(), JSON_PRETTY_PRINT);