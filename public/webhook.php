<?php

declare(strict_types=1);

/**
 * SOLPI Webhook — endpoint público para Evolution API (WhatsApp).
 *
 * URL: http://glpi/solpi/webhook.php
 * Montado em: /var/www/glpi/public/solpi/webhook.php
 *
 * Aceita POST JSON da Evolution API v2.
 * Valida apikey no payload e cria ticket no GLPI.
 */

define('GLPI_ROOT', dirname(__DIR__, 4) . DIRECTORY_SEPARATOR);

if (!file_exists(GLPI_ROOT . 'inc/includes.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'GLPI_ROOT not found: ' . GLPI_ROOT]);
    exit;
}

include GLPI_ROOT . 'inc/includes.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$raw     = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validar chave da API
$receivedKey = $payload['apikey'] ?? ($_SERVER['HTTP_APIKEY'] ?? '');

require_once GLPI_ROOT . 'plugins/solpi/inc/bootstrap.php';

use SOLPI\Core\Config;

$cfg = new Config();
$cfg->load();
$expectedKey = $cfg->get('evolution.auth_key', 'solpi123');

if ($receivedKey !== $expectedKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

use SOLPI\Modules\WhatsApp\WhatsAppController;

try {
    $controller = new WhatsAppController();
    $result     = $controller->handleWebhook($payload);
    http_response_code(200);
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
}