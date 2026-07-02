<?php

declare(strict_types=1);

$glpiRoot = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

if (!is_file($glpiRoot . 'inc/includes.php')) {
	http_response_code(500);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'GLPI bootstrap not found']);
	exit;
}

// Compatibilidade com GLPI 11+: inicializa autoload e conexao DB global.
$autoload = $glpiRoot . 'vendor/autoload.php';
if (is_file($autoload)) {
	require_once $autoload;
}

if (!defined('GLPI_ROOT')) {
	define('GLPI_ROOT', $glpiRoot);
}

$glpiRootPath = rtrim((string)GLPI_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!defined('GLPI_CONFIG_DIR')) {
	$configCandidates = [
		'/var/glpi/config',
		$glpiRootPath . 'config',
	];

	foreach ($configCandidates as $candidate) {
		if (is_dir($candidate)) {
			define('GLPI_CONFIG_DIR', rtrim($candidate, DIRECTORY_SEPARATOR));
			break;
		}
	}
}

if (class_exists('DBConnection')) {
	$configDir = defined('GLPI_CONFIG_DIR') ? GLPI_CONFIG_DIR : ($glpiRootPath . 'config' . DIRECTORY_SEPARATOR);
	$configDb = rtrim((string)$configDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config_db.php';

	if (is_file($configDb)) {
		include_once $configDb;

		if (class_exists('DB', false)) {
			DBConnection::establishDBConnection(false, false);
		}
	}
}

require_once $glpiRootPath . 'inc/includes.php';
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

