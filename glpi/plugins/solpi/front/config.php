<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

$constants = require __DIR__ . '/../config/constants.php';
$config = require __DIR__ . '/../config/config.php';

$settings = [
    'Plugin Name' => $constants['solpi_name'] ?? 'SOLPI',
    'Plugin Version' => $constants['solpi_version'] ?? 'unknown',
    'AI Enabled' => $config['ai']['enabled'] ?? 'unknown',
    'Zabbix Enabled' => $config['zabbix']['enabled'] ?? 'unknown',
    'WhatsApp Enabled' => $config['whatsapp']['enabled'] ?? 'unknown',
];

include __DIR__ . '/../templates/layouts/header.php';
include __DIR__ . '/../templates/settings.php';
include __DIR__ . '/../templates/layouts/footer.php';

