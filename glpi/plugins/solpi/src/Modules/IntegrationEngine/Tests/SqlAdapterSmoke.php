<?php

declare(strict_types=1);

require_once __DIR__ . '/../Adapters/SourceAdapterInterface.php';
require_once __DIR__ . '/../Adapters/SqlAdapter.php';

use SOLPI\Modules\IntegrationEngine\Adapters\SqlAdapter;

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, 'pdo_sqlite is required for SqlAdapterSmoke.' . PHP_EOL);
    exit(1);
}

$databasePath = sys_get_temp_dir() . '/solpi_sql_adapter_smoke_' . uniqid('', true) . '.sqlite';
$cleanup = static function () use ($databasePath): void {
    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
};
register_shutdown_function($cleanup);

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE companies (id INTEGER PRIMARY KEY, updated_at TEXT NOT NULL, name TEXT NOT NULL)');
$pdo->exec("INSERT INTO companies (id, updated_at, name) VALUES (1, '2026-01-01', 'One')");
$pdo->exec("INSERT INTO companies (id, updated_at, name) VALUES (2, '2026-01-02', 'Two')");
$pdo->exec("INSERT INTO companies (id, updated_at, name) VALUES (3, '2026-01-03', 'Three')");

$adapter = new SqlAdapter();

$paginated = $adapter->ingest([
    'dsn' => 'sqlite:' . $databasePath,
    'query' => 'SELECT id, updated_at, name FROM companies ORDER BY id',
    'pagination' => [
        'enabled' => true,
        'page_size' => 2,
        'max_pages' => 5,
        'start_offset' => 0,
        'stop_when_empty' => true,
        'stop_when_short_page' => true,
    ],
]);

$incremental = $adapter->ingest([
    'dsn' => 'sqlite:' . $databasePath,
    'query' => 'SELECT id, updated_at, name FROM companies ORDER BY id',
    'incremental' => [
        'enabled' => true,
        'column' => 'updated_at',
        'value' => '2026-01-01'
    ],
    'params' => [],
]);

if (($paginated['meta']['count'] ?? null) !== 3) {
    fwrite(STDERR, 'Paginated count is invalid.' . PHP_EOL);
    exit(1);
}

if (($paginated['meta']['pages_fetched'] ?? null) !== 2) {
    fwrite(STDERR, 'Paginated pages_fetched is invalid.' . PHP_EOL);
    exit(1);
}

if (($paginated['meta']['stop_when_short_page'] ?? null) !== true) {
    fwrite(STDERR, 'Paginated stop_when_short_page is invalid.' . PHP_EOL);
    exit(1);
}

if (($incremental['meta']['incremental_column'] ?? null) !== 'updated_at') {
    fwrite(STDERR, 'Incremental column is invalid.' . PHP_EOL);
    exit(1);
}

if (($incremental['meta']['count'] ?? null) !== 2) {
    fwrite(STDERR, 'Incremental count is invalid.' . PHP_EOL);
    exit(1);
}

if (($incremental['meta']['max_incremental_value'] ?? null) !== '2026-01-03') {
    fwrite(STDERR, 'Incremental max value is invalid.' . PHP_EOL);
    exit(1);
}

echo 'SqlAdapterSmoke OK' . PHP_EOL;