<?php

declare(strict_types=1);

define('GLPI_ROOT', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);

if (!is_file(GLPI_ROOT . 'inc/includes.php')) {
	http_response_code(500);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'GLPI bootstrap not found']);
	exit;
}

require_once GLPI_ROOT . 'inc/includes.php';
require_once __DIR__ . '/../inc/bootstrap.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/routes.php';

try {
	$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
	$path = solpi_api_path();
	$payload = solpi_api_json_body();
	$query = is_array($_GET) ? $_GET : [];

	$routes = solpi_api_routes();
	$handler = $routes[$method][$path] ?? null;

	if (!is_callable($handler)) {
		solpi_api_send([
			'error' => 'Route not found',
			'method' => $method,
			'path' => $path,
		], 404);
	}

	if ($path !== '/health' && $path !== '/') {
		solpi_api_require_auth($payload);
	}

	$result = $handler($payload, $query);

	if (!is_array($result)) {
		$result = ['data' => $result];
	}

	solpi_api_send($result, 200);
} catch (Throwable $e) {
	solpi_api_send([
		'error' => $e->getMessage(),
		'file' => basename($e->getFile()),
		'line' => $e->getLine(),
	], 500);
}

