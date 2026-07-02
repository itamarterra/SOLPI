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
 *     --threshold-pct=10
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
$historyExit = runCommand([
    escapeshellarg(PHP_BINARY),
    escapeshellarg($historyRunner),
    '--base-url=' . escapeshellarg($baseUrl),
    '--api-key=' . escapeshellarg($apiKey),
    '--sizes=' . escapeshellarg($sizes),
    '--batch-size=' . (string)$batchSize,
    '--worker-limit=' . (string)$workerLimit,
    '--history-file=' . escapeshellarg($historyFile),
]);

if ($historyExit !== 0) {
    fwrite(STDERR, 'Daily runner aborted: history step failed with exit code ' . $historyExit . PHP_EOL);
    exit($historyExit);
}

echo PHP_EOL . '=== Step 2/2: trend report ===' . PHP_EOL;
$trendExit = runCommand([
    escapeshellarg(PHP_BINARY),
    escapeshellarg($trendRunner),
    '--history-file=' . escapeshellarg($historyFile),
    '--last=' . (string)$last,
    '--threshold-pct=' . (string)$thresholdPct,
]);

if ($trendExit !== 0) {
    fwrite(STDERR, 'Daily runner finished with trend failure, exit code ' . $trendExit . PHP_EOL);
    exit($trendExit);
}

echo PHP_EOL . 'Daily benchmark workflow completed successfully.' . PHP_EOL;
exit(0);

/**
 * @param array<int,string> $parts
 */
function runCommand(array $parts): int
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
        return 1;
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($stdout !== '') {
        echo $stdout;
    }

    if ($stderr !== '') {
        fwrite(STDERR, $stderr);
    }

    return $exitCode;
}
