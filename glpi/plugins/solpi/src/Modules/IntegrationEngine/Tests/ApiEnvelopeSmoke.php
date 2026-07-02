<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../api/middleware.php';

$success = solpi_api_success(['items' => [1, 2, 3]], ['source' => 'smoke']);
$error = solpi_api_error('Unauthorized', 401, ['hint' => 'apikey missing']);

if (($success['status'] ?? null) !== 'ok') {
    fwrite(STDERR, 'Success envelope status is invalid.' . PHP_EOL);
    exit(1);
}

if (!isset($success['data']['items']) || $success['data']['items'] !== [1, 2, 3]) {
    fwrite(STDERR, 'Success envelope data is invalid.' . PHP_EOL);
    exit(1);
}

if (($error['status'] ?? null) !== 'error') {
    fwrite(STDERR, 'Error envelope status is invalid.' . PHP_EOL);
    exit(1);
}

if (($error['error']['code'] ?? null) !== 401 || ($error['error']['message'] ?? null) !== 'Unauthorized') {
    fwrite(STDERR, 'Error envelope payload is invalid.' . PHP_EOL);
    exit(1);
}

echo 'ApiEnvelopeSmoke OK' . PHP_EOL;
