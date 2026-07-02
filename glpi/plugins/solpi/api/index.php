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
		solpi_api_send(solpi_api_error('Route not found', 404, [
			'method' => $method,
			'path' => $path,
		]), 404);
	}

	if ($path !== '/health' && $path !== '/') {
		solpi_api_require_auth($payload);
	}

	$result = $handler($payload, $query);

	if (!is_array($result)) {
		$result = ['data' => $result];
	}

	if (array_key_exists('error', $result)) {
		$errorDetails = [];
		if (is_string($result['error'])) {
			$errorDetails['message'] = $result['error'];
		} elseif (is_array($result['error'])) {
			$errorDetails = $result['error'];
		}

		solpi_api_send(solpi_api_error((string)($errorDetails['message'] ?? 'Error'), 400, $errorDetails), 400);
	}

	solpi_api_send(solpi_api_success($result), 200);
} catch (Throwable $e) {
	solpi_api_send(solpi_api_error($e->getMessage(), 500, [
		'file' => basename($e->getFile()),
		'line' => $e->getLine(),
	]), 500);
}

