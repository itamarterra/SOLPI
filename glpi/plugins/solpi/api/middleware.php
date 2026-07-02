<?php

declare(strict_types=1);

use SOLPI\Core\Config;

/**
 * @return array<string, string>
 */
function solpi_api_headers(): array
{
	if (function_exists('getallheaders')) {
		$headers = getallheaders();
		if (is_array($headers)) {
			return $headers;
		}
	}

	$headers = [];
	foreach ($_SERVER as $key => $value) {
		if (!str_starts_with($key, 'HTTP_')) {
			continue;
		}

		$name = str_replace('_', '-', strtolower(substr($key, 5)));
		$headers[$name] = (string)$value;
	}

	return $headers;
}

function solpi_api_path(): string
{
	$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
	$path = is_string($path) ? $path : '/';

	$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

	if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
		$path = substr($path, strlen($scriptDir));
	}

	$path = '/' . ltrim($path, '/');

	if (str_starts_with($path, '/index.php/')) {
		$path = '/' . ltrim(substr($path, strlen('/index.php/')), '/');
	}

	return $path === '/index.php' ? '/' : $path;
}

/**
 * @return array<string,mixed>
 */
function solpi_api_json_body(): array
{
	$raw = file_get_contents('php://input');
	if ($raw === false || trim($raw) === '') {
		return [];
	}

	$decoded = json_decode($raw, true);

	return is_array($decoded) ? $decoded : [];
}

function solpi_api_send(array $data, int $status = 200): never
{
	http_response_code($status);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function solpi_api_require_auth(array $payload = []): void
{
	$config = new Config();
	$config->load();

	$expected = (string)($config->get('evolution.auth_key') ?: getenv('SOLPI_WEBHOOK_SECRET') ?: '');
	if ($expected === '') {
		solpi_api_send(['error' => 'API secret is not configured'], 503);
	}

	$headers = solpi_api_headers();
	$provided = (string)(
		$payload['apikey']
		?? $payload['api_key']
		?? $headers['x-api-key']
		?? $headers['apikey']
		?? $_SERVER['HTTP_X_API_KEY']
		?? $_SERVER['HTTP_APIKEY']
		?? ''
	);

	if (!hash_equals($expected, $provided)) {
		solpi_api_send(['error' => 'Unauthorized'], 401);
	}
}

