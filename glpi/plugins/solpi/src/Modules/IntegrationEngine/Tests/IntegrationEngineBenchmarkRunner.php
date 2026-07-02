<?php

declare(strict_types=1);

/**
 * Benchmark runner para IntegrationEngine API.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkRunner.php \
 *     --base-url="http://localhost:8081/solpi/index.php" \
 *     --api-key="SEU_SEGREDO" \
 *     --sizes="250,500,1000,2000" \
 *     --batch-size=250 \
 *     --worker-limit=300
 */

$options = getopt('', ['base-url:', 'api-key:', 'sizes::', 'batch-size::', 'worker-limit::']);
$baseUrl = (string)($options['base-url'] ?? '');
$apiKey = (string)($options['api-key'] ?? '');
$sizesRaw = (string)($options['sizes'] ?? '250,500,1000,2000');
$batchSize = max(1, min(1000, (int)($options['batch-size'] ?? 250)));
$workerLimit = max(1, min(2000, (int)($options['worker-limit'] ?? 300)));

if ($baseUrl === '' || $apiKey === '') {
    fwrite(STDERR, "Missing required arguments --base-url and --api-key\n");
    exit(1);
}

$sizes = array_values(array_filter(array_map(
    static fn (string $value): int => max(1, (int)trim($value)),
    explode(',', $sizesRaw)
)));

if ($sizes === []) {
    fwrite(STDERR, "Invalid --sizes argument. Example: --sizes=250,500,1000\n");
    exit(1);
}

$loadRunner = __DIR__ . '/IntegrationEngineLoadRunner.php';
if (!is_file($loadRunner)) {
    fwrite(STDERR, 'Load runner not found: ' . $loadRunner . PHP_EOL);
    exit(1);
}

$rows = [];
$failed = false;

foreach ($sizes as $size) {
    $command = implode(' ', [
        escapeshellarg(PHP_BINARY),
        escapeshellarg($loadRunner),
        '--base-url=' . escapeshellarg($baseUrl),
        '--api-key=' . escapeshellarg($apiKey),
        '--records=' . (string)$size,
        '--batch-size=' . (string)$batchSize,
        '--worker-limit=' . (string)$workerLimit,
    ]);

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        fwrite(STDERR, 'Failed to start process for size=' . $size . PHP_EOL);
        $failed = true;
        continue;
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    $decoded = json_decode((string)$stdout, true);
    $status = is_array($decoded) ? (string)($decoded['status'] ?? 'error') : 'error';

    if ($exitCode !== 0 || !is_array($decoded) || $status !== 'ok') {
        $failed = true;
        $rows[] = [
            'records' => $size,
            'status' => 'error',
            'enqueue_seconds' => null,
            'worker_seconds' => null,
            'total_seconds' => null,
            'throughput_records_per_sec' => null,
            'worker_runs' => null,
            'worker_processed_total' => null,
            'error' => trim($stderr) !== '' ? trim($stderr) : 'load runner execution failed',
        ];
        continue;
    }

    $rows[] = [
        'records' => (int)($decoded['requested_records'] ?? $size),
        'status' => 'ok',
        'enqueue_seconds' => (float)($decoded['enqueue_seconds'] ?? 0.0),
        'worker_seconds' => (float)($decoded['worker_seconds'] ?? 0.0),
        'total_seconds' => (float)($decoded['total_seconds'] ?? 0.0),
        'throughput_records_per_sec' => (float)($decoded['throughput_records_per_sec'] ?? 0.0),
        'worker_runs' => (int)($decoded['worker_runs'] ?? 0),
        'worker_processed_total' => (int)($decoded['worker_processed_total'] ?? 0),
        'error' => '',
    ];
}

echo 'Benchmark IntegrationEngine (batch-size=' . $batchSize . ', worker-limit=' . $workerLimit . ')' . PHP_EOL;
echo '| records | status | enqueue_s | worker_s | total_s | throughput_rec_s | worker_runs | processed |' . PHP_EOL;
echo '|---:|:---:|---:|---:|---:|---:|---:|---:|' . PHP_EOL;

foreach ($rows as $row) {
    echo sprintf(
        '| %d | %s | %s | %s | %s | %s | %s | %s |',
        (int)$row['records'],
        (string)$row['status'],
        valueOrDash($row['enqueue_seconds']),
        valueOrDash($row['worker_seconds']),
        valueOrDash($row['total_seconds']),
        valueOrDash($row['throughput_records_per_sec']),
        valueOrDash($row['worker_runs']),
        valueOrDash($row['worker_processed_total'])
    ) . PHP_EOL;

    if ((string)$row['error'] !== '') {
        echo '  error: ' . (string)$row['error'] . PHP_EOL;
    }
}

echo PHP_EOL . json_encode(['rows' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($failed ? 2 : 0);

/**
 * @param mixed $value
 */
function valueOrDash($value): string
{
    if ($value === null) {
        return '-';
    }

    if (is_float($value)) {
        return number_format($value, 3, '.', '');
    }

    return (string)$value;
}
