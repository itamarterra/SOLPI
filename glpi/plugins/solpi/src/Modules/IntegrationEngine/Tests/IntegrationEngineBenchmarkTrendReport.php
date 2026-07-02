<?php

declare(strict_types=1);

/**
 * Gera relatorio de tendencia a partir do historico JSONL de benchmark.
 *
 * Uso:
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkTrendReport.php
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkTrendReport.php --history-file="logs/integration_engine_benchmark_history.jsonl" --last=7
 *   php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkTrendReport.php --threshold-pct=10
 */

$options = getopt('', ['history-file::', 'last::', 'threshold-pct::']);

$pluginRoot = dirname(__DIR__, 4);
$defaultHistoryFile = $pluginRoot . '/logs/integration_engine_benchmark_history.jsonl';
$historyFile = (string)($options['history-file'] ?? $defaultHistoryFile);
$last = max(2, (int)($options['last'] ?? 10));
$thresholdPct = max(0.0, (float)($options['threshold-pct'] ?? 0.0));

if (!is_file($historyFile)) {
    fwrite(STDERR, 'History file not found: ' . $historyFile . PHP_EOL);
    exit(1);
}

$lines = file($historyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!is_array($lines) || $lines === []) {
    fwrite(STDERR, 'History file is empty: ' . $historyFile . PHP_EOL);
    exit(1);
}

$entries = [];
foreach ($lines as $line) {
    $decoded = json_decode($line, true);
    if (!is_array($decoded)) {
        continue;
    }

    $entries[] = $decoded;
}

if ($entries === []) {
    fwrite(STDERR, 'No valid JSON entries in history file.' . PHP_EOL);
    exit(1);
}

$entries = array_slice($entries, -$last);
$latest = $entries[array_key_last($entries)];
$previous = count($entries) > 1 ? $entries[count($entries) - 2] : null;

$latestRows = normalizeRows(is_array($latest['rows'] ?? null) ? $latest['rows'] : []);
$previousRows = normalizeRows(is_array($previous['rows'] ?? null) ? $previous['rows'] : []);

$recordSizes = array_keys($latestRows);
sort($recordSizes);

echo 'IntegrationEngine Benchmark Trend' . PHP_EOL;
echo 'history_file: ' . $historyFile . PHP_EOL;
echo 'entries_analyzed: ' . count($entries) . PHP_EOL;
echo 'latest_recorded_at: ' . (string)($latest['recorded_at'] ?? '-') . PHP_EOL;
if ($previous !== null) {
    echo 'previous_recorded_at: ' . (string)($previous['recorded_at'] ?? '-') . PHP_EOL;
}

echo PHP_EOL;
echo '| records | latest_throughput | prev_throughput | delta_abs | delta_pct | latest_total_s | prev_total_s |' . PHP_EOL;
echo '|---:|---:|---:|---:|---:|---:|---:|' . PHP_EOL;

$deltas = [];
$deltasPct = [];
$regressionDetected = false;
$regressionSizes = [];
$rowsOutput = [];
foreach ($recordSizes as $size) {
    $latestRow = $latestRows[$size];
    $prevRow = $previousRows[$size] ?? null;

    $latestTp = (float)($latestRow['throughput_records_per_sec'] ?? 0.0);
    $prevTp = $prevRow !== null ? (float)($prevRow['throughput_records_per_sec'] ?? 0.0) : 0.0;

    $deltaAbs = $prevRow !== null ? ($latestTp - $prevTp) : null;
    $deltaPct = ($prevRow !== null && $prevTp > 0.0) ? (($deltaAbs / $prevTp) * 100.0) : null;

    $latestTotal = (float)($latestRow['total_seconds'] ?? 0.0);
    $prevTotal = $prevRow !== null ? (float)($prevRow['total_seconds'] ?? 0.0) : null;

    echo sprintf(
        '| %d | %s | %s | %s | %s | %s | %s |',
        $size,
        formatFloat($latestTp),
        formatNullableFloat($prevTp, $prevRow !== null),
        formatNullableFloat($deltaAbs, $deltaAbs !== null),
        formatNullablePercent($deltaPct, $deltaPct !== null),
        formatFloat($latestTotal),
        formatNullableFloat($prevTotal, $prevTotal !== null)
    ) . PHP_EOL;

    if ($deltaAbs !== null) {
        $deltas[] = $deltaAbs;
    }

    if ($deltaPct !== null) {
        $deltasPct[] = $deltaPct;
        if ($thresholdPct > 0.0 && $deltaPct <= (-1.0 * $thresholdPct)) {
            $regressionDetected = true;
            $regressionSizes[] = $size;
        }
    }

    $rowsOutput[] = [
        'records' => $size,
        'latest_throughput' => $latestTp,
        'previous_throughput' => $prevRow !== null ? $prevTp : null,
        'delta_abs' => $deltaAbs,
        'delta_pct' => $deltaPct,
        'latest_total_seconds' => $latestTotal,
        'previous_total_seconds' => $prevTotal,
    ];
}

echo PHP_EOL;
$meanDelta = null;
if ($deltas !== []) {
    $meanDelta = array_sum($deltas) / count($deltas);
    echo 'mean_delta_throughput_abs: ' . formatFloat($meanDelta) . ' rec/s' . PHP_EOL;
}

$meanDeltaPct = null;
if ($deltasPct !== []) {
    $meanDeltaPct = array_sum($deltasPct) / count($deltasPct);
    echo 'mean_delta_throughput_pct: ' . formatNullablePercent($meanDeltaPct, true) . PHP_EOL;
}

if ($thresholdPct > 0.0) {
    echo 'threshold_pct: ' . formatNullablePercent($thresholdPct, true) . PHP_EOL;
    echo 'regression_detected: ' . ($regressionDetected ? 'true' : 'false') . PHP_EOL;
    if ($regressionDetected) {
        echo 'regression_sizes: ' . implode(',', $regressionSizes) . PHP_EOL;
    }
}

echo json_encode([
    'latest_recorded_at' => $latest['recorded_at'] ?? null,
    'previous_recorded_at' => $previous['recorded_at'] ?? null,
    'sizes' => $recordSizes,
    'rows' => $rowsOutput,
    'mean_delta_throughput_abs' => $meanDelta,
    'mean_delta_throughput_pct' => $meanDeltaPct,
    'threshold_pct' => $thresholdPct,
    'regression_detected' => $regressionDetected,
    'regression_sizes' => $regressionSizes,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

if ($thresholdPct > 0.0 && $regressionDetected) {
    exit(3);
}

exit(0);

/**
 * @param array<int,array<string,mixed>> $rows
 * @return array<int,array<string,mixed>>
 */
function normalizeRows(array $rows): array
{
    $indexed = [];

    foreach ($rows as $row) {
        $records = (int)($row['records'] ?? 0);
        if ($records <= 0) {
            continue;
        }

        $indexed[$records] = $row;
    }

    return $indexed;
}

function formatFloat(float $value): string
{
    return number_format($value, 3, '.', '');
}

function formatNullableFloat(?float $value, bool $enabled): string
{
    if (!$enabled || $value === null) {
        return '-';
    }

    return formatFloat($value);
}

function formatNullablePercent(?float $value, bool $enabled): string
{
    if (!$enabled || $value === null) {
        return '-';
    }

    return number_format($value, 2, '.', '') . '%';
}
