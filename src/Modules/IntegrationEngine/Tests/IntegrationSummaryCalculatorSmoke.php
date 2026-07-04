<?php

declare(strict_types=1);

require_once __DIR__ . '/../Services/IntegrationSummaryCalculator.php';

use SOLPI\Modules\IntegrationEngine\Services\IntegrationSummaryCalculator;

$calculator = new IntegrationSummaryCalculator();
$summary = $calculator->summarizeJobs([
    [
        'payload' => [
            '_queue_meta' => [
                'ingestion_run_id' => 'run-A',
                'batch_count' => 1,
                'batch_total' => 2,
                'batch_size' => 250,
                'records_total' => 10,
                'records_queued' => 10,
                'records_duplicate' => 0,
                'truncated' => false,
                'checkpoint_enabled' => true,
            ],
        ],
    ],
    [
        'payload' => [
            '_queue_meta' => [
                'ingestion_run_id' => 'run-A',
                'batch_count' => 2,
                'batch_total' => 2,
                'batch_size' => 250,
                'records_total' => 10,
                'records_queued' => 10,
                'records_duplicate' => 0,
                'truncated' => false,
                'checkpoint_enabled' => false,
            ],
        ],
    ],
    [
        'payload' => [
            '_queue_meta' => [
                'ingestion_run_id' => 'run-B',
                'batch_count' => 1,
                'batch_total' => 1,
                'batch_size' => 250,
                'records_total' => 8,
                'records_queued' => 7,
                'records_duplicate' => 1,
                'truncated' => true,
                'checkpoint_enabled' => false,
            ],
        ],
    ],
]);

$expected = [
    'jobs_with_meta' => 3,
    'truncated_jobs' => 1,
    'checkpoint_jobs' => 1,
    'records_total' => 18,
    'records_queued' => 17,
    'records_duplicate' => 1,
    'batch_count_max' => 2,
    'batch_total_max' => 2,
    'batch_size_max' => 250,
];

foreach ($expected as $key => $value) {
    if (($summary[$key] ?? null) !== $value) {
        fwrite(STDERR, sprintf('Unexpected %s value.', $key) . PHP_EOL);
        exit(1);
    }
}

echo 'IntegrationSummaryCalculatorSmoke OK' . PHP_EOL;
