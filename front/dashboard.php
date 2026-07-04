<?php

declare(strict_types=1);

use SOLPI\Modules\Dashboard\DashboardController;

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

$controller = new DashboardController();

$controller->index();

