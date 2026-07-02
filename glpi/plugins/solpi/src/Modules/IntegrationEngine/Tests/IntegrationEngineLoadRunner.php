<?php

declare(strict_types=1);

/**
 * Load runner para IntegrationEngine API.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineLoadRunner.php \
 *     --base-url="http://localhost:8081/solpi/index.php" \
 *     --api-key="SEU_SEGREDO" \
 *     --records=1000 \
 *     --batch-size=250 \
 *     --worker-limit=300
 */

$options = getopt('', ['base-url:', 'api-key:', 'records::', 'batch-size::', 'worker-limit::']);
$baseUrl = (string)($options['base-url'] ?? '');
$apiKey = (string)($options['api-key'] ?? '');
$records = max(1, (int)($options['records'] ?? 1000));
$batchSize = max(1, min(1000, (int)($options['batch-size'] ?? 250)));
$workerLimit = max(1, min(2000, (int)($options['worker-limit'] ?? 300)));

if ($baseUrl === '' || $apiKey === '') {
    fwrite(STDERR, "Missing required arguments --base-url and --api-key\n");
    exit(1);
}

$runId = date('His') . substr(bin2hex(random_bytes(3)), 0, 6);
$source = 'srl_' . $runId;
$startedAt = microtime(true);

$dataset = [];
for ($i = 1; $i <= $records; $i++) {
    $dataset[] = [
        'entity_type' => 'user',
        'email' => 'load.user' . $i . '.' . $runId . '@acme.test',
        'name' => 'Load User ' . $i . ' ' . $runId,
        'updated_at' => gmdate('Y-m-d\\TH:i:s\\Z', strtotime('2026-07-02T12:00:00Z') + $i),
    ];
}

$enqueueStarted = microtime(true);
$enqueueResponse = request(
    $baseUrl,
    '/integration-engine/ingest/adapter',
    'POST',
    [
        'apikey' => $apiKey,
        'source' => $source,
        'event' => 'upsert',
        'adapter' => 'json',
        'context' => [
            'batch_size' => $batchSize,
            'max_records' => $records,
            'checkpoint' => [
                'enabled' => true,
                'name' => 'lp_' . $runId,
            ],
        ],
        'payload' => [
            'checkpoint_field' => 'updated_at',
            'data' => $dataset,
        ],
    ]
);
$enqueueSeconds = microtime(true) - $enqueueStarted;

if ($enqueueResponse['status'] < 200 || $enqueueResponse['status'] >= 300) {
    fwrite(STDERR, 'Enqueue failed: HTTP ' . $enqueueResponse['status'] . PHP_EOL);
    printPayload($enqueueResponse['payload']);
    exit(2);
}

$enqueueData = is_array($enqueueResponse['payload']['data'] ?? null) ? $enqueueResponse['payload']['data'] : [];
$queuedJobs = is_array($enqueueData['job_ids'] ?? null) ? $enqueueData['job_ids'] : [];
$recordsTotal = (int)($enqueueData['records_total'] ?? 0);
$recordsQueued = (int)($enqueueData['records_queued'] ?? 0);

if ($recordsTotal < $records || $recordsQueued < 1) {
    fwrite(STDERR, 'Enqueue validation failed: records_total/records_queued unexpected.' . PHP_EOL);
    printPayload($enqueueResponse['payload']);
    exit(3);
}

$workerStarted = microtime(true);
$processedTotal = 0;
$workerRuns = 0;
$idleRuns = 0;

for ($i = 0; $i < 30; $i++) {
    $run = request($baseUrl, '/integration-engine/worker/run-once', 'POST', [
        'apikey' => $apiKey,
        'limit' => $workerLimit,
    ]);

    if ($run['status'] < 200 || $run['status'] >= 300) {
        fwrite(STDERR, 'Worker run failed: HTTP ' . $run['status'] . PHP_EOL);
        printPayload($run['payload']);
        exit(4);
    }

    $runData = is_array($run['payload']['data'] ?? null) ? $run['payload']['data'] : [];
    $processed = (int)($runData['processed'] ?? 0);
    $workerRuns++;
    $processedTotal += $processed;

    if ($processed === 0) {
        $idleRuns++;
    } else {
        $idleRuns = 0;
    }

    if ($idleRuns >= 2) {
        break;
    }
}

$workerSeconds = microtime(true) - $workerStarted;

$jobsResponse = request($baseUrl, '/integration-engine/jobs?limit=200', 'GET', [
    'apikey' => $apiKey,
]);

if ($jobsResponse['status'] < 200 || $jobsResponse['status'] >= 300) {
    fwrite(STDERR, 'Jobs listing failed: HTTP ' . $jobsResponse['status'] . PHP_EOL);
    printPayload($jobsResponse['payload']);
    exit(5);
}

$jobs = is_array($jobsResponse['payload']['data']['items'] ?? null) ? $jobsResponse['payload']['data']['items'] : [];
$jobsScanLimit = count($jobs);
$jobsFromRun = [];

foreach ($jobs as $job) {
    $payload = is_array($job['payload'] ?? null) ? $job['payload'] : [];
    if ((string)($payload['source'] ?? '') === $source) {
        $jobsFromRun[] = $job;
    }
}

if ($jobsFromRun === []) {
    fwrite(STDERR, 'No jobs found for current load run source.' . PHP_EOL);
    exit(6);
}

$jobsWithMeta = 0;
$jobsWithCheckpoint = 0;
$jobsWithTruncated = 0;

foreach ($jobsFromRun as $job) {
    $payload = is_array($job['payload'] ?? null) ? $job['payload'] : [];
    $meta = is_array($payload['_queue_meta'] ?? null) ? $payload['_queue_meta'] : [];
    if ($meta !== []) {
        $jobsWithMeta++;
        if (!empty($meta['checkpoint_enabled'])) {
            $jobsWithCheckpoint++;
        }
        if (!empty($meta['truncated'])) {
            $jobsWithTruncated++;
        }
    }
}

$summaryResponse = request($baseUrl, '/integration-engine/summary', 'GET', [
    'apikey' => $apiKey,
]);

if ($summaryResponse['status'] < 200 || $summaryResponse['status'] >= 300) {
    fwrite(STDERR, 'Summary failed: HTTP ' . $summaryResponse['status'] . PHP_EOL);
    printPayload($summaryResponse['payload']);
    exit(7);
}

$summary = is_array($summaryResponse['payload']['data'] ?? null) ? $summaryResponse['payload']['data'] : [];
$batches = is_array($summary['batches'] ?? null) ? $summary['batches'] : [];

$totalSeconds = microtime(true) - $startedAt;
$throughput = $recordsQueued > 0 && $totalSeconds > 0
    ? round($recordsQueued / $totalSeconds, 2)
    : 0.0;

$result = [
    'status' => 'ok',
    'run_id' => $runId,
    'source' => $source,
    'requested_records' => $records,
    'records_total' => $recordsTotal,
    'records_queued' => $recordsQueued,
    'job_ids_count' => count($queuedJobs),
    'enqueue_seconds' => round($enqueueSeconds, 3),
    'worker_seconds' => round($workerSeconds, 3),
    'total_seconds' => round($totalSeconds, 3),
    'worker_runs' => $workerRuns,
    'worker_processed_total' => $processedTotal,
    'throughput_records_per_sec' => $throughput,
    'jobs_scan_limit' => $jobsScanLimit,
    'jobs_found_in_scan_for_source' => count($jobsFromRun),
    'jobs_with_meta_for_source' => $jobsWithMeta,
    'jobs_with_checkpoint_for_source' => $jobsWithCheckpoint,
    'jobs_with_truncated_for_source' => $jobsWithTruncated,
    'summary_batches' => [
        'jobs_with_meta' => (int)($batches['jobs_with_meta'] ?? 0),
        'truncated_jobs' => (int)($batches['truncated_jobs'] ?? 0),
        'checkpoint_jobs' => (int)($batches['checkpoint_jobs'] ?? 0),
        'records_total' => (int)($batches['records_total'] ?? 0),
        'records_queued' => (int)($batches['records_queued'] ?? 0),
        'records_duplicate' => (int)($batches['records_duplicate'] ?? 0),
        'batch_count_max' => (int)($batches['batch_count_max'] ?? 0),
        'batch_total_max' => (int)($batches['batch_total_max'] ?? 0),
        'batch_size_max' => (int)($batches['batch_size_max'] ?? 0),
    ],
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

if ($jobsWithMeta < 1) {
    fwrite(STDERR, 'Validation failed: expected jobs_with_meta_for_source >= 1' . PHP_EOL);
    exit(8);
}

if ((int)($result['summary_batches']['batch_size_max'] ?? 0) < $batchSize) {
    fwrite(STDERR, 'Validation warning: summary batch_size_max is lower than requested batch-size.' . PHP_EOL);
}

exit(0);

/**
 * @param array<string,mixed> $body
 * @return array{status:int,payload:mixed}
 */
function request(string $baseUrl, string $path, string $method, array $body): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL extension is required for load runner.');
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);

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

/**
 * @param mixed $payload
 */
function printPayload($payload): void
{
    if (is_array($payload)) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        return;
    }

    echo (string)$payload . PHP_EOL;
}
