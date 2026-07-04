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

// Debug log (avoid storing raw payloads or PII). Log event, message type and a payload hash only.
$msgType = $payload['data']['messageType'] ?? '';
$msgKeys = array_keys($payload['data']['message'] ?? []);
$payloadHash = hash('sha256', $raw);
file_put_contents('/var/glpi/logs/solpi_webhook.log',
    date('[Y-m-d H:i:s] ')
    . "event=" . ($payload['event'] ?? '')
    . " type={$msgType} msgKeys=[" . implode(',', $msgKeys) . "]"
    . " payload_hash={$payloadHash}"
    . "\n",
    FILE_APPEND
);

// Authentication: require HMAC signature header `X-SOLPI-SIGNATURE`
// Shared secret must be provided via environment variable `SOLPI_WEBHOOK_SECRET`.
$signatureHeader = $_SERVER['HTTP_X_SOLPI_SIGNATURE'] ?? ($_SERVER['X-SOLPI-SIGNATURE'] ?? '');
$secret = getenv('SOLPI_WEBHOOK_SECRET') ?: '';

if (empty($secret) || empty($signatureHeader)) {
    // Missing secret or signature — deny to avoid fallback to weak auth.
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Validate HMAC-SHA256 signature of raw payload
$expected = hash_hmac('sha256', $raw, $secret);
// Use hash_equals to mitigate timing attacks
if (!hash_equals($expected, $signatureHeader)) {
    // Record auth failure with payload hash only
    file_put_contents('/var/glpi/logs/webhook_debug.log',
        date('[Y-m-d H:i:s] ') . "AUTH FAIL - payload_hash=" . hash('sha256', $raw) . "\n",
        FILE_APPEND
    );
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $result = (new SOLPI\Modules\WhatsApp\WhatsAppController())->handleWebhook($payload);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
}