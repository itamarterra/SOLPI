<?php

declare(strict_types=1);

use SOLPI\Controllers\SettingsController;

include('../../../inc/includes.php');

$controller = new SettingsController();

$settings = $controller->index();

include(dirname(__DIR__) . '/templates/settings.php');
