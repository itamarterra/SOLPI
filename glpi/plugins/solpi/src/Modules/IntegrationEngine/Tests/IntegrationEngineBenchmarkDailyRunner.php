<?php

declare(strict_types=1);

/**
 * Runner diario: executa baseline historico e em seguida gera relatorio de tendencia.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkDailyRunner.php \
 *     --base-url="http://localhost:8081/solpi/index.php" \
 *     --api-key="SEU_SEGREDO" \
 *     --sizes="250,500,1000,2000" \
 *     --batch-size=250 \
 *     --worker-limit=300 \
 *     --last=7 \
 *     --threshold-pct=10 \
 *     --report-json-file="logs/integration_engine_benchmark_latest.json" \
 *     --report-md-file="logs/integration_engine_benchmark_latest.md"
 */

$options = getopt('', [
    'base-url:',
    'api-key:',
    'sizes::',
    'batch-size::',
    'worker-limit::',
    'history-file::',
    'last::',
    'threshold-pct::',
    'report-json-file::',
    'report-md-file::',
]);

$baseUrl = (string)($options['base-url'] ?? '');
$apiKey = (string)($options['api-key'] ?? '');
$sizes = (string)($options['sizes'] ?? '250,500,1000,2000');
$batchSize = max(1, min(1000, (int)($options['batch-size'] ?? 250)));
$workerLimit = max(1, min(2000, (int)($options['worker-limit'] ?? 300)));
$last = max(2, (int)($options['last'] ?? 7));
$thresholdPct = max(0.0, (float)($options['threshold-pct'] ?? 0.0));

$pluginRoot = dirname(__DIR__, 4);
$defaultHistoryFile = $pluginRoot . '/logs/integration_engine_benchmark_history.jsonl';
$historyFile = (string)($options['history-file'] ?? $defaultHistoryFile);
$reportJsonFile = (string)($options['report-json-file'] ?? ($pluginRoot . '/logs/integration_engine_benchmark_latest.json'));
$reportMdFile = (string)($options['report-md-file'] ?? ($pluginRoot . '/logs/integration_engine_benchmark_latest.md'));

if ($baseUrl === '' || $apiKey === '') {
    fwrite(STDERR, "Missing required arguments --base-url and --api-key\n");
    exit(1);
}

$historyRunner = __DIR__ . '/IntegrationEngineBenchmarkHistoryRunner.php';
$trendRunner = __DIR__ . '/IntegrationEngineBenchmarkTrendReport.php';

if (!is_file($historyRunner)) {
    fwrite(STDERR, 'History runner not found: ' . $historyRunner . PHP_EOL);
    exit(1);
}

if (!is_file($trendRunner)) {
    fwrite(STDERR, 'Trend runner not found: ' . $trendRunner . PHP_EOL);
    exit(1);
}

echo '=== Step 1/2: benchmark baseline + history ===' . PHP_EOL;
$historyResult = runCommand([
    escapeshellarg(PHP_BINARY),
    escapeshellarg($historyRunner),
    '--base-url=' . escapeshellarg($baseUrl),
    '--api-key=' . escapeshellarg($apiKey),
    '--sizes=' . escapeshellarg($sizes),
    '--batch-size=' . (string)$batchSize,
    '--worker-limit=' . (string)$workerLimit,
    '--history-file=' . escapeshellarg($historyFile),
]);

if ($historyResult['stdout'] !== '') {
    echo $historyResult['stdout'];
}

if ($historyResult['stderr'] !== '') {
    fwrite(STDERR, $historyResult['stderr']);
}

if ($historyResult['exitCode'] !== 0) {
    fwrite(STDERR, 'Daily runner aborted: history step failed with exit code ' . $historyResult['exitCode'] . PHP_EOL);
    exit($historyResult['exitCode']);
}

echo PHP_EOL . '=== Step 2/2: trend report ===' . PHP_EOL;
$trendResult = runCommand([
    escapeshellarg(PHP_BINARY),
    escapeshellarg($trendRunner),
    '--history-file=' . escapeshellarg($historyFile),
    '--last=' . (string)$last,
    '--threshold-pct=' . (string)$thresholdPct,
]);

if ($trendResult['stdout'] !== '') {
    echo $trendResult['stdout'];
}

if ($trendResult['stderr'] !== '') {
    fwrite(STDERR, $trendResult['stderr']);
}

$trendPayload = extractJsonPayload($trendResult['stdout']);
$report = [
    'executed_at' => date(DATE_ATOM),
    'status' => $trendResult['exitCode'] === 0 ? 'ok' : 'alert',
    'base_url' => $baseUrl,
    'sizes' => $sizes,
    'batch_size' => $batchSize,
    'worker_limit' => $workerLimit,
    'last' => $last,
    'threshold_pct' => $thresholdPct,
    'history_file' => $historyFile,
    'trend_exit_code' => $trendResult['exitCode'],
    'trend' => $trendPayload,
];

writeJsonReport($reportJsonFile, $report);
writeMarkdownReport($reportMdFile, $report);

echo PHP_EOL;
echo 'Latest JSON report: ' . $reportJsonFile . PHP_EOL;
echo 'Latest Markdown report: ' . $reportMdFile . PHP_EOL;

if ($trendResult['exitCode'] !== 0) {
    fwrite(STDERR, 'Daily runner finished with trend failure, exit code ' . $trendResult['exitCode'] . PHP_EOL);
    exit($trendResult['exitCode']);
}

echo PHP_EOL . 'Daily benchmark workflow completed successfully.' . PHP_EOL;
exit(0);

/**
 * @param array<int,string> $parts
 * @return array{exitCode:int,stdout:string,stderr:string}
 */
function runCommand(array $parts): array
{
    $command = implode(' ', $parts);
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        fwrite(STDERR, 'Failed to start process: ' . $command . PHP_EOL);
        return [
            'exitCode' => 1,
            'stdout' => '',
            'stderr' => 'Failed to start process: ' . $command,
        ];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'exitCode' => $exitCode,
        'stdout' => is_string($stdout) ? $stdout : '',
        'stderr' => is_string($stderr) ? $stderr : '',
    ];
}

/**
 * @return array<string,mixed>|null
 */
function extractJsonPayload(string $stdout): ?array
{
    $needle = '{"latest_recorded_at"';
    $jsonStart = strrpos($stdout, $needle);

    if ($jsonStart === false) {
        $needle = "{\"latest_recorded_at\"";
        $jsonStart = strrpos($stdout, $needle);
    }

    if ($jsonStart === false) {
        return null;
    }

    $jsonChunk = trim(substr($stdout, $jsonStart));
    $decoded = json_decode($jsonChunk, true);

    return is_array($decoded) ? $decoded : null;
}

/**
 * @param array<string,mixed> $report
 */
function writeJsonReport(string $path, array $report): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create report directory: ' . $dir);
    }

    $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Unable to encode JSON report.');
    }

    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('Unable to write JSON report: ' . $path);
    }
}

/**
 * @param array<string,mixed> $report
 */
function writeMarkdownReport(string $path, array $report): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create report directory: ' . $dir);
    }

    $trend = is_array($report['trend'] ?? null) ? $report['trend'] : [];
    $rows = is_array($trend['rows'] ?? null) ? $trend['rows'] : [];

    $lines = [];
    $lines[] = '# IntegrationEngine Daily Benchmark Report';
    $lines[] = '';
    $lines[] = '- executed_at: ' . (string)($report['executed_at'] ?? '-');
    $lines[] = '- status: ' . (string)($report['status'] ?? '-');
    $lines[] = '- threshold_pct: ' . (string)($report['threshold_pct'] ?? 0);
    $lines[] = '- trend_exit_code: ' . (string)($report['trend_exit_code'] ?? '-');
    $lines[] = '- latest_recorded_at: ' . (string)($trend['latest_recorded_at'] ?? '-');
    $lines[] = '- previous_recorded_at: ' . (string)($trend['previous_recorded_at'] ?? '-');
    $lines[] = '';
    $lines[] = '| records | latest_tp | prev_tp | delta_abs | delta_pct | latest_total_s | prev_total_s |';
    $lines[] = '|---:|---:|---:|---:|---:|---:|---:|';

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $lines[] = sprintf(
            '| %d | %s | %s | %s | %s | %s | %s |',
            (int)($row['records'] ?? 0),
            mdValue($row['latest_throughput'] ?? null),
            mdValue($row['previous_throughput'] ?? null),
            mdValue($row['delta_abs'] ?? null),
            mdPercent($row['delta_pct'] ?? null),
            mdValue($row['latest_total_seconds'] ?? null),
            mdValue($row['previous_total_seconds'] ?? null)
        );
    }

    if (($trend['regression_detected'] ?? false) === true) {
        $sizes = is_array($trend['regression_sizes'] ?? null) ? implode(',', $trend['regression_sizes']) : '-';
        $lines[] = '';
        $lines[] = 'Regression detected on sizes: ' . $sizes;
    }

    if (file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL) === false) {
        throw new RuntimeException('Unable to write Markdown report: ' . $path);
    }
}

/**
 * @param mixed $value
 */
function mdValue($value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    if (is_numeric($value)) {
        return number_format((float)$value, 3, '.', '');
    }

    return (string)$value;
}

/**
 * @param mixed $value
 */
function mdPercent($value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    if (is_numeric($value)) {
        return number_format((float)$value, 2, '.', '') . '%';
    }

    return (string)$value;
}
