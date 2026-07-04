<?php

declare(strict_types=1);

require_once __DIR__ . '/../Services/BatchContextService.php';

use SOLPI\Modules\IntegrationEngine\Services\BatchContextService;

$service = new BatchContextService();
$meta = $service->build(
    'rest',
    'smoke_source',
    'upsert',
    250,
    0,
    1,
    1,
    0,
    2,
    10,
    10,
    0,
    false,
    [
        'enabled' => true,
        'name' => 'companies_sync',
        'in' => 'cursor-001',
        'out' => 'cursor-002',
    ]
);

$requiredKeys = [
    'adapter',
    'source',
    'event',
    'batch_size',
    'batch_index',
    'batch_count',
    'batch_total',
    'batch_jobs_in_chunk',
    'job_index',
    'records_total',
    'records_queued',
    'records_duplicate',
    'truncated',
    'checkpoint_enabled',
    'checkpoint_name',
    'checkpoint_in',
    'checkpoint_out',
];

$missing = [];
foreach ($requiredKeys as $key) {
    if (!array_key_exists($key, $meta)) {
        $missing[] = $key;
    }
}

if ($missing !== []) {
    fwrite(STDERR, 'Missing keys: ' . implode(', ', $missing) . PHP_EOL);
    exit(1);
}

if ($meta['adapter'] !== 'rest' || $meta['batch_size'] !== 250 || $meta['batch_total'] !== 1 || $meta['batch_jobs_in_chunk'] !== 2) {
    fwrite(STDERR, 'Unexpected batch metadata values.' . PHP_EOL);
    exit(1);
}

if ($meta['checkpoint_enabled'] !== true || $meta['checkpoint_name'] !== 'companies_sync') {
    fwrite(STDERR, 'Unexpected checkpoint metadata values.' . PHP_EOL);
    exit(1);
}

$truncatedMeta = $service->build(
    'json',
    'smoke_source',
    'upsert',
    1,
    0,
    1,
    1,
    0,
    1,
    5,
    1,
    4,
    true,
    [
        'enabled' => true,
        'name' => 'truncated_smoke',
        'in' => '2026-01-01T00:00:00Z',
        'out' => '2026-01-02T00:00:00Z',
    ]
);

if ($truncatedMeta['truncated'] !== true || $truncatedMeta['records_total'] !== 5 || $truncatedMeta['records_queued'] !== 1 || $truncatedMeta['records_duplicate'] !== 4) {
    fwrite(STDERR, 'Unexpected truncated metadata values.' . PHP_EOL);
    exit(1);
}

echo 'BatchContextSmoke OK' . PHP_EOL;
