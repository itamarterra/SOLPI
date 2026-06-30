<?php

declare(strict_types=1);

header('Content-Type: application/json');

try {
    require_once '/var/www/glpi/vendor/autoload.php';
    require_once '/var/glpi/config/config_db.php';
    require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

    global $DB;
    $DB = new DB();

} catch (Throwable $e) {
    echo json_encode(['error' => 'Bootstrap: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$raw     = (string)file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON', 'raw' => substr($raw, 0, 200)]);
    exit;
}

// Log de debug: registra tipo de mensagem recebida (util para diagnostico)
$msgType = $payload['data']['messageType'] ?? '';
$msgKeys = array_keys($payload['data']['message'] ?? []);
file_put_contents('/var/glpi/logs/solpi_webhook.log',
    date('[Y-m-d H:i:s] ')
    . "event={$payload['event']} type={$msgType} msgKeys=[" . implode(',', $msgKeys) . "]"
    . " fromMe=" . json_encode($payload['data']['key']['fromMe'] ?? null)
    . "\n",
    FILE_APPEND
);

// Validacao: aceita se tiver apikey correta OU se vier da instancia 'solpi'
$apiKey   = $payload['apikey'] ?? ($_SERVER['HTTP_APIKEY'] ?? '');
$instance = $payload['instance'] ?? '';

$validKey      = ($apiKey === 'solpi123');
$validInstance = ($instance === 'solpi');

if (!$validKey && !$validInstance) {
    // Log para debug
    file_put_contents('/var/glpi/logs/webhook_debug.log',
        date('[Y-m-d H:i:s] ') . "AUTH FAIL - key='{$apiKey}' instance='{$instance}'\n" .
        "PAYLOAD: " . substr($raw, 0, 500) . "\n\n",
        FILE_APPEND
    );
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'received_key' => $apiKey, 'instance' => $instance]);
    exit;
}

try {
    $result = (new SOLPI\Modules\WhatsApp\WhatsAppController())->handleWebhook($payload);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
}