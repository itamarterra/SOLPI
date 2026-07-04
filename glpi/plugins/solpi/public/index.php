<?php

declare(strict_types=1);

/**
 * Bridge publico para a API interna do plugin SOLPI.
 *
 * Exemplo:
 *   /solpi/index.php/health
 *   /solpi/index.php/integration-engine/adapters
 */

$apiEntrypoint = realpath(__DIR__ . '/../../plugins/solpi/api/index.php');
if ($apiEntrypoint === false || !is_file($apiEntrypoint)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'SOLPI API entrypoint not found']);
    exit;
}

require $apiEntrypoint;
