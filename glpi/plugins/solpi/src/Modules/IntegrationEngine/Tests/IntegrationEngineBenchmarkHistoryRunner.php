<?php

declare(strict_types=1);

/**
 * Runner de baseline historico para IntegrationEngine.
 *
 * Executa o benchmark comparativo e grava uma linha JSON no historico.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkHistoryRunner.php \
 *     --base-url="http://localhost:8081/solpi/index.php" \
 *     --api-key="SEU_SEGREDO" \
 *     --sizes="250,500,1000,2000" \
 *     --batch-size=250 \
 *     --worker-limit=300
 */

$options = getopt('', [
    'base-url:',
    'api-key:',
    'sizes::',
    'batch-size::',
    'worker-limit::',
    'history-file::',
]);

$baseUrl = (string)($options['base-url'] ?? '');
$apiKey = (string)($options['api-key'] ?? '');
$sizes = (string)($options['sizes'] ?? '250,500,1000,2000');
$batchSize = max(1, min(1000, (int)($options['batch-size'] ?? 250)));
$workerLimit = max(1, min(2000, (int)($options['worker-limit'] ?? 300)));

$pluginRoot = dirname(__DIR__, 4);
$defaultHistoryFile = $pluginRoot . '/logs/integration_engine_benchmark_history.jsonl';
$historyFile = (string)($options['history-file'] ?? $defaultHistoryFile);

if ($baseUrl === '' || $apiKey === '') {
    fwrite(STDERR, "Missing required arguments --base-url and --api-key\n");
    exit(1);
}

$benchmarkRunner = __DIR__ . '/IntegrationEngineBenchmarkRunner.php';
if (!is_file($benchmarkRunner)) {
    fwrite(STDERR, 'Benchmark runner not found: ' . $benchmarkRunner . PHP_EOL);
    exit(1);
}

$command = implode(' ', [
    escapeshellarg(PHP_BINARY),
    escapeshellarg($benchmarkRunner),
    '--base-url=' . escapeshellarg($baseUrl),
    '--api-key=' . escapeshellarg($apiKey),
    '--sizes=' . escapeshellarg($sizes),
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
    fwrite(STDERR, 'Failed to start benchmark runner.' . PHP_EOL);
    exit(1);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
fclose($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);
$exitCode = proc_close($process);

// Mantem a saida original para uso operacional.
echo $stdout;
if (trim($stderr) !== '') {
    fwrite(STDERR, $stderr);
}

if ($exitCode !== 0) {
    fwrite(STDERR, 'Benchmark runner failed with exit code ' . $exitCode . PHP_EOL);
    exit($exitCode);
}

$payload = extractJsonPayload($stdout);
if (!is_array($payload) || !isset($payload['rows']) || !is_array($payload['rows'])) {
    fwrite(STDERR, 'Unable to parse benchmark JSON output.' . PHP_EOL);
    exit(2);
}

$entry = [
    'recorded_at' => date(DATE_ATOM),
    'base_url' => $baseUrl,
    'sizes' => $sizes,
    'batch_size' => $batchSize,
    'worker_limit' => $workerLimit,
    'rows' => $payload['rows'],
];

$dir = dirname($historyFile);
if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
    fwrite(STDERR, 'Unable to create history directory: ' . $dir . PHP_EOL);
    exit(3);
}

$line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($line === false) {
    fwrite(STDERR, 'Unable to encode history entry.' . PHP_EOL);
    exit(4);
}

if (file_put_contents($historyFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
    fwrite(STDERR, 'Unable to write history file: ' . $historyFile . PHP_EOL);
    exit(5);
}

echo 'History saved to: ' . $historyFile . PHP_EOL;
exit(0);

/**
 * @return array<string,mixed>|null
 */
function extractJsonPayload(string $stdout): ?array
{
    $needle = '{"rows"';
    $jsonStart = strrpos($stdout, $needle);

    if ($jsonStart === false) {
        $needle = "{\n    \"rows\"";
        $jsonStart = strrpos($stdout, $needle);
    }

    if ($jsonStart === false) {
        return null;
    }

    $jsonChunk = trim(substr($stdout, $jsonStart));
    $decoded = json_decode($jsonChunk, true);

    return is_array($decoded) ? $decoded : null;
}
