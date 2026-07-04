<?php

declare(strict_types=1);

use SOLPI\Modules\Settings\SettingsController;

include('../../../inc/includes.php');

$controller = new SettingsController();

$settings = $controller->list('core');

if (isset($settings['items']) && is_array($settings['items'])) {
	$settings = $settings['items'];
}

include(dirname(__DIR__) . '/templates/settings.php');
