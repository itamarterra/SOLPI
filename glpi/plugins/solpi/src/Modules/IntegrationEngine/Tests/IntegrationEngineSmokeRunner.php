<?php

declare(strict_types=1);

/**
 * Smoke runner simples para IntegrationEngine API.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeRunner.php \
 *     --base-url="http://localhost/glpi/plugins/solpi/api/index.php" \
 *     --api-key="SEU_SEGREDO"
 */

$options = getopt('', ['base-url:', 'api-key:']);
$baseUrl = (string)($options['base-url'] ?? '');
$apiKey = (string)($options['api-key'] ?? '');

if ($baseUrl === '' || $apiKey === '') {
    fwrite(STDERR, "Missing required arguments --base-url and --api-key\n");
    exit(1);
}

$steps = [
    [
        'name' => 'list adapters',
        'method' => 'GET',
        'path' => '/integration-engine/adapters',
        'body' => ['apikey' => $apiKey],
    ],
    [
        'name' => 'ingest baseline',
        'method' => 'POST',
        'path' => '/integration-engine/ingest',
        'body' => [
            'apikey' => $apiKey,
            'source' => 'smoke_runner_baseline',
            'event' => 'upsert',
            'payload' => [
                'entity_type' => 'company',
                'name' => 'ACME Smoke Runner',
                'email' => 'smoke.runner@acme.test',
            ],
        ],
    ],
    [
        'name' => 'ingest via json adapter',
        'method' => 'POST',
        'path' => '/integration-engine/ingest/adapter',
        'body' => [
            'apikey' => $apiKey,
            'source' => 'smoke_runner_json',
            'event' => 'upsert',
            'adapter' => 'json',
            'payload' => [
                'data' => [
                    [
                        'entity_type' => 'user',
                        'email' => 'runner.user1@acme.test',
                        'name' => 'Runner User 1',
                    ],
                    [
                        'entity_type' => 'user',
                        'email' => 'runner.user2@acme.test',
                        'name' => 'Runner User 2',
                    ],
                ],
            ],
        ],
    ],
    [
        'name' => 'run worker once',
        'method' => 'POST',
        'path' => '/integration-engine/worker/run-once',
        'body' => [
            'apikey' => $apiKey,
            'limit' => 20,
        ],
    ],
    [
        'name' => 'classify text',
        'method' => 'POST',
        'path' => '/integration-engine/classify',
        'body' => [
            'apikey' => $apiKey,
            'record' => [
                'correlation_id' => 'smoke-classification-' . date('YmdHis'),
                'text' => 'Servidor indisponivel com erro critico apos atualizacao de release.',
            ],
        ],
    ],
    [
        'name' => 'list jobs',
        'method' => 'GET',
        'path' => '/integration-engine/jobs?limit=10',
        'body' => ['apikey' => $apiKey],
    ],
    [
        'name' => 'integration summary',
        'method' => 'GET',
        'path' => '/integration-engine/summary',
        'body' => ['apikey' => $apiKey],
    ],
];

$failed = false;

foreach ($steps as $step) {
    $result = request($baseUrl, $step['path'], $step['method'], $step['body']);
    $status = (int)$result['status'];
    $payload = $result['payload'];

    $line = sprintf('[%s] %s %s -> HTTP %d', $status >= 200 && $status < 300 ? 'OK' : 'ERR', strtoupper($step['method']), $step['path'], $status);
    echo $line . PHP_EOL;

    if ($status < 200 || $status >= 300) {
        $failed = true;
    }

    if (is_array($payload)) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    } else {
        echo (string)$payload . PHP_EOL;
    }

    echo str_repeat('-', 90) . PHP_EOL;
}

exit($failed ? 2 : 0);

/**
 * @param array<string,mixed> $body
 * @return array{status:int,payload:mixed}
 */
function request(string $baseUrl, string $path, string $method, array $body): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL extension is required for smoke runner.');
    }

    $url = rtrim($baseUrl, '/') . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if (strtoupper($method) !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Request failed: ' . $error);
    }

    $decoded = json_decode($response, true);

    return [
        'status' => $status,
        'payload' => is_array($decoded) ? $decoded : $response,
    ];
}
