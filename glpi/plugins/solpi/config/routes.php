<?php

declare(strict_types=1);

return [
	'front' => [
		'home' => '/plugins/solpi/front/index.php',
		'dashboard' => '/plugins/solpi/front/dashboard.php',
		'tickets' => '/plugins/solpi/front/tickets.php',
		'alerts' => '/plugins/solpi/front/alerts.php',
		'config' => '/plugins/solpi/front/config.php',
	],
	'api' => [
		'base' => '/plugins/solpi/api/index.php',
		'health' => '/plugins/solpi/api/index.php/health',
		'webhook_whatsapp' => '/plugins/solpi/api/index.php/webhook/whatsapp',
	],
];

