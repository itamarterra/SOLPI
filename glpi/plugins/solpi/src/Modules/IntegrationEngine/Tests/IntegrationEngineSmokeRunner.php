<?php

declare(strict_types=1);

/**
 * Smoke runner simples para IntegrationEngine API.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeRunner.php \
 *     --base-url="http://localhost:8081/solpi/index.php" \
 *     --api-key="SEU_SEGREDO"
 */

$options = getopt('', ['base-url:', 'api-key:']);
$baseUrl = (string)($options['base-url'] ?? '');
$apiKey = (string)($options['api-key'] ?? '');

if ($baseUrl === '' || $apiKey === '') {
    fwrite(STDERR, "Missing required arguments --base-url and --api-key\n");
    exit(1);
}

$runId = date('His') . substr(bin2hex(random_bytes(3)), 0, 6);

$steps = [
    [
        'name' => 'list adapters',
        'method' => 'GET',
        'path' => '/integration-engine/adapters',
        'body' => ['apikey' => $apiKey],
        'expect' => static function (array $response): array {
            $items = $response['data']['items'] ?? null;
            if (!is_array($items) || $items === []) {
                return [false, 'missing adapters list'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'ingest baseline',
        'method' => 'POST',
        'path' => '/integration-engine/ingest',
        'body' => [
            'apikey' => $apiKey,
            'source' => 'srb_' . $runId,
            'event' => 'upsert',
            'payload' => [
                'entity_type' => 'company',
                'name' => 'ACME Smoke Runner ' . $runId,
                'email' => 'smoke.runner.' . $runId . '@acme.test',
            ],
        ],
        'expect' => static function (array $response): array {
            $status = (string)($response['data']['status'] ?? '');
            if ($status !== 'queued' && $status !== 'duplicate') {
                return [false, 'expected status queued|duplicate'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'ingest via json adapter',
        'method' => 'POST',
        'path' => '/integration-engine/ingest/adapter',
        'body' => [
            'apikey' => $apiKey,
            'source' => 'srj_' . $runId,
            'event' => 'upsert',
            'adapter' => 'json',
            'payload' => [
                'data' => [
                    [
                        'entity_type' => 'user',
                        'email' => 'runner.user1.' . $runId . '@acme.test',
                        'name' => 'Runner User 1 ' . $runId,
                    ],
                    [
                        'entity_type' => 'user',
                        'email' => 'runner.user2.' . $runId . '@acme.test',
                        'name' => 'Runner User 2 ' . $runId,
                    ],
                ],
            ],
        ],
        'expect' => static function (array $response): array {
            $data = is_array($response['data'] ?? null) ? $response['data'] : [];
            $status = (string)($data['status'] ?? '');
            if ($status !== 'queued') {
                return [false, 'expected status queued'];
            }

            if (!isset($data['records_total']) || (int)$data['records_total'] < 1) {
                return [false, 'records_total should be >= 1'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'ingest json truncated checkpoint',
        'method' => 'POST',
        'path' => '/integration-engine/ingest/adapter',
        'body' => [
            'apikey' => $apiKey,
            'source' => 'srt_' . $runId,
            'event' => 'upsert',
            'adapter' => 'json',
            'context' => [
                'max_records' => 1,
                'checkpoint' => [
                    'enabled' => true,
                    'name' => 'cp_' . $runId,
                ],
            ],
            'payload' => [
                'checkpoint_field' => 'updated_at',
                'data' => [
                    [
                        'entity_type' => 'user',
                        'email' => 'runner.batch1.' . $runId . '@acme.test',
                        'name' => 'Runner Batch 1 ' . $runId,
                        'updated_at' => '2026-07-02T12:00:00Z',
                    ],
                    [
                        'entity_type' => 'user',
                        'email' => 'runner.batch2.' . $runId . '@acme.test',
                        'name' => 'Runner Batch 2 ' . $runId,
                        'updated_at' => '2026-07-02T12:05:00Z',
                    ],
                ],
            ],
        ],
        'expect' => static function (array $response): array {
            $data = is_array($response['data'] ?? null) ? $response['data'] : [];
            if (($data['truncated'] ?? null) !== true) {
                return [false, 'expected truncated=true'];
            }

            if (($data['checkpoint_enabled'] ?? null) !== true) {
                return [false, 'expected checkpoint_enabled=true'];
            }

            $checkpointOut = (string)($data['checkpoint_out'] ?? '');
            if ($checkpointOut === '') {
                return [false, 'expected checkpoint_out to be filled'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'run worker once',
        'method' => 'POST',
        'path' => '/integration-engine/worker/run-once',
        'body' => [
            'apikey' => $apiKey,
            'limit' => 20,
        ],
        'expect' => static function (array $response): array {
            $status = (string)($response['data']['status'] ?? '');
            if ($status !== 'ok') {
                return [false, 'expected status ok'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'classify text',
        'method' => 'POST',
        'path' => '/integration-engine/classify',
        'body' => [
            'apikey' => $apiKey,
            'record' => [
                'correlation_id' => 'smoke-classification-' . $runId,
                'text' => 'Servidor indisponivel com erro critico apos atualizacao de release.',
            ],
        ],
        'expect' => static function (array $response): array {
            $status = (string)($response['data']['status'] ?? '');
            if ($status !== 'classified' && $status !== 'review_required') {
                return [false, 'expected status classified|review_required'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'list jobs',
        'method' => 'GET',
        'path' => '/integration-engine/jobs?limit=10',
        'body' => ['apikey' => $apiKey],
        'expect' => static function (array $response): array {
            $items = $response['data']['items'] ?? null;
            if (!is_array($items)) {
                return [false, 'expected jobs.items array'];
            }

            return [true, ''];
        },
    ],
    [
        'name' => 'integration summary',
        'method' => 'GET',
        'path' => '/integration-engine/summary',
        'body' => ['apikey' => $apiKey],
        'expect' => static function (array $response): array {
            $summary = $response['data'] ?? null;
            if (!is_array($summary)) {
                return [false, 'expected summary object'];
            }

            if (!isset($summary['jobs']) || !isset($summary['batches'])) {
                return [false, 'missing jobs or batches in summary'];
            }

            $batches = is_array($summary['batches']) ? $summary['batches'] : [];
            if ((int)($batches['jobs_with_meta'] ?? 0) < 1) {
                return [false, 'expected batches.jobs_with_meta >= 1'];
            }

            if ((int)($batches['checkpoint_jobs'] ?? 0) < 1) {
                return [false, 'expected batches.checkpoint_jobs >= 1'];
            }

            if ((int)($batches['truncated_jobs'] ?? 0) < 1) {
                return [false, 'expected batches.truncated_jobs >= 1'];
            }

            return [true, ''];
        },
    ],
];

$failed = false;

foreach ($steps as $step) {
    $result = request($baseUrl, $step['path'], $step['method'], $step['body']);
    $status = (int)$result['status'];
    $payload = $result['payload'];

    $expect = $step['expect'] ?? null;
    $expectOk = true;
    $expectReason = '';
    if (is_callable($expect) && is_array($payload)) {
        $expectResult = $expect($payload);
        if (is_array($expectResult)) {
            $expectOk = (bool)($expectResult[0] ?? false);
            $expectReason = (string)($expectResult[1] ?? 'unexpected response');
        }
    }

    $isOk = $status >= 200 && $status < 300 && $expectOk;
    $line = sprintf('[%s] %s %s -> HTTP %d', $isOk ? 'OK' : 'ERR', strtoupper($step['method']), $step['path'], $status);
    echo $line . PHP_EOL;

    if ($status < 200 || $status >= 300 || !$expectOk) {
        $failed = true;
    }

    if (!$expectOk) {
        echo 'Expectation failed: ' . $expectReason . PHP_EOL;
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
    $headers = ['Content-Type: application/json'];

    $apiKey = (string)($body['apikey'] ?? $body['api_key'] ?? '');
    if ($apiKey !== '') {
        $headers[] = 'X-API-Key: ' . $apiKey;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if (strtoupper($method) === 'GET') {
        if ($body !== []) {
            $queryBody = $body;
            unset($queryBody['apikey'], $queryBody['api_key']);

            $query = http_build_query($queryBody);
            if ($query !== '') {
                curl_setopt($ch, CURLOPT_URL, $url . (str_contains($url, '?') ? '&' : '?') . $query);
            }
        }
    } else {
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
