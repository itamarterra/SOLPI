<?php

declare(strict_types=1);

include('../../../inc/includes.php');

Session::checkLoginUser();

global $DB;

use SOLPI\Modules\Dashboard\DashboardStatistics;

$statistics = new DashboardStatistics($DB);

header('Content-Type: application/json');

echo json_encode(
    $statistics->load(),
    JSON_PRETTY_PRINT
);