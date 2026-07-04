<?php

declare(strict_types=1);

/**
 * Webhook SOLPI — recebe eventos da Evolution API (WhatsApp).
 *
 * URL configurada na Evolution API:
 *   http://glpi/plugins/solpi/ajax/webhook.php
 *
 * Aceita POST com Content-Type: application/json.
 * Valida a chave da API no payload antes de processar.
 */

define('GLPI_ROOT', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);

if (!file_exists(GLPI_ROOT . 'inc/includes.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'GLPI not found']);
    exit;
}

include GLPI_ROOT . 'inc/includes.php';

// Apenas aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Lê o corpo da requisição
$raw     = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

// Valida a chave da Evolution API no payload
$receivedKey = $payload['apikey'] ?? ($_SERVER['HTTP_APIKEY'] ?? '');

require_once __DIR__ . '/../inc/bootstrap.php';

use SOLPI\Core\Config;

$config = new Config();
$config->load();

$expectedKey = $config->get('evolution.auth_key', 'solpi123');

if ($receivedKey !== $expectedKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Processa o webhook
use SOLPI\Modules\WhatsApp\WhatsAppController;

header('Content-Type: application/json');

try {
    $controller = new WhatsAppController();
    $result     = $controller->handleWebhook($payload);

    http_response_code(200);
    echo json_encode($result);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => $e->getMessage(),
        'file'    => basename($e->getFile()),
        'line'    => $e->getLine(),
    ]);
}