<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../stubs/dbmysql.php';
require_once __DIR__ . '/../../../stubs/ramsey_uuid.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'SOLPI\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = __DIR__ . '/../../../' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

final class WorkerFailureSmokeDb extends \DBmysql
{
    /** @var array<string,array<int,array<string,mixed>>> */
    private array $tables = [
        'glpi_plugin_solpi_jobs' => [],
        'glpi_plugin_solpi_dead_letter' => [],
        'glpi_plugin_solpi_logs' => [],
        'glpi_plugin_solpi_merge_conflicts' => [],
    ];

    private int $lastInsertId = 0;

    public function seedJob(array $row): void
    {
        $this->tables['glpi_plugin_solpi_jobs'][] = $row;
    }

    public function insert(string $table, array $data): bool
    {
        $this->lastInsertId++;
        $data['id'] = $this->lastInsertId;
        $this->tables[$table][] = $data;

        return true;
    }

    public function insertId(): int
    {
        return $this->lastInsertId;
    }

    public function update(string $table, array $data, array $where = []): bool
    {
        foreach ($this->tables[$table] ?? [] as $index => $row) {
            if (!$this->matches($row, $where)) {
                continue;
            }

            $this->tables[$table][$index] = array_merge($row, $data);
        }

        return true;
    }

    public function delete(string $table, array $where = []): bool
    {
        foreach ($this->tables[$table] ?? [] as $index => $row) {
            if ($this->matches($row, $where)) {
                unset($this->tables[$table][$index]);
            }
        }

        $this->tables[$table] = array_values($this->tables[$table] ?? []);

        return true;
    }

    public function request(mixed $sql): iterable
    {
        if (!is_array($sql)) {
            return [];
        }

        $table = (string)($sql['FROM'] ?? '');
        $rows = $this->tables[$table] ?? [];

        if (isset($sql['WHERE']) && is_array($sql['WHERE'])) {
            $rows = array_values(array_filter($rows, fn (array $row): bool => $this->matches($row, $sql['WHERE'])));
        }

        if (isset($sql['ORDER']) && $sql['ORDER'] === 'id DESC') {
            usort($rows, static fn (array $left, array $right): int => (int)($right['id'] ?? 0) <=> (int)($left['id'] ?? 0));
        }

        if (isset($sql['ORDER']) && $sql['ORDER'] === 'id ASC') {
            usort($rows, static fn (array $left, array $right): int => (int)($left['id'] ?? 0) <=> (int)($right['id'] ?? 0));
        }

        if (isset($sql['ORDER']) && $sql['ORDER'] === 'scheduled_at ASC') {
            usort($rows, static fn (array $left, array $right): int => strcmp((string)($left['scheduled_at'] ?? ''), (string)($right['scheduled_at'] ?? '')));
        }

        $limit = isset($sql['LIMIT']) ? (int)$sql['LIMIT'] : count($rows);

        return array_slice($rows, 0, max(0, $limit));
    }

    public function query(string $sql): bool
    {
        if (stripos($sql, 'UPDATE glpi_plugin_solpi_jobs') === 0) {
            if (preg_match('/WHERE id = (\d+)/', $sql, $matches) !== 1) {
                return true;
            }

            $jobId = (int)$matches[1];
            foreach ($this->tables['glpi_plugin_solpi_jobs'] as $index => $row) {
                if ((int)($row['id'] ?? 0) !== $jobId) {
                    continue;
                }

                if (str_contains($sql, 'status = "RUNNING"')) {
                    $this->tables['glpi_plugin_solpi_jobs'][$index]['status'] = 'RUNNING';
                    $this->tables['glpi_plugin_solpi_jobs'][$index]['attempts'] = (int)($row['attempts'] ?? 0) + 1;
                    $this->tables['glpi_plugin_solpi_jobs'][$index]['started_at'] = date('Y-m-d H:i:s');
                    break;
                }

                if (str_contains($sql, 'CASE WHEN attempts >= max_attempts THEN "DEAD" ELSE "PENDING" END')) {
                    $attempts = (int)($row['attempts'] ?? 0);
                    $maxAttempts = (int)($row['max_attempts'] ?? 1);
                    $this->tables['glpi_plugin_solpi_jobs'][$index]['status'] = $attempts >= $maxAttempts ? 'DEAD' : 'PENDING';

                    if (preg_match('/error = "(.*?)", finished_at = NOW\(\) WHERE id = (\d+)/', $sql, $errorMatch) === 1) {
                        $this->tables['glpi_plugin_solpi_jobs'][$index]['error'] = stripslashes($errorMatch[1]);
                    }

                    $this->tables['glpi_plugin_solpi_jobs'][$index]['finished_at'] = date('Y-m-d H:i:s');
                    break;
                }
            }
        }

        return true;
    }

    public function escape(string $value): string
    {
        return addslashes($value);
    }

    /**
     * @param array<string,mixed> $row
     * @param array<string,mixed> $where
     */
    private function matches(array $row, array $where): bool
    {
        foreach ($where as $key => $value) {
            if (($row[$key] ?? null) !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function table(string $name): array
    {
        return array_values($this->tables[$name] ?? []);
    }
}

$DB = new WorkerFailureSmokeDb();
global $DB;

$DB->seedJob([
    'id' => 1,
    'name' => 'invalid_entity_type',
    'handler' => 'IntegrationEngineWorker@process',
    'payload' => json_encode([
        'correlation_id' => 'smoke-worker-failure-001',
        'payload' => [
            'entity_type' => 'unsupported_entity',
            'name' => 'Broken payload',
        ],
    ], JSON_UNESCAPED_UNICODE),
    'status' => 'PENDING',
    'attempts' => 0,
    'max_attempts' => 1,
    'scheduled_at' => '2026-07-02 00:00:00',
]);

$worker = new \SOLPI\Modules\IntegrationEngine\Workers\IntegrationEngineWorker();
$processed = $worker->runOnce(10);

if ($processed !== 0) {
    fwrite(STDERR, 'Worker processed unexpected jobs.' . PHP_EOL);
    exit(1);
}

$jobs = $DB->table('glpi_plugin_solpi_jobs');
$deadLetters = $DB->table('glpi_plugin_solpi_dead_letter');
$logs = $DB->table('glpi_plugin_solpi_logs');

if (($jobs[0]['status'] ?? null) !== 'DEAD') {
    fwrite(STDERR, 'Job was not marked as DEAD.' . PHP_EOL);
    exit(1);
}

if (count($deadLetters) !== 1) {
    fwrite(STDERR, 'Dead letter was not created.' . PHP_EOL);
    exit(1);
}

if (!str_contains((string)($deadLetters[0]['error'] ?? ''), 'Unsupported entity_type for matcher')) {
    fwrite(STDERR, 'Dead letter error message is invalid.' . PHP_EOL);
    exit(1);
}

if (count($logs) !== 1) {
    fwrite(STDERR, 'Worker error log was not written.' . PHP_EOL);
    exit(1);
}

$deadLetterService = new \SOLPI\Modules\IntegrationEngine\Services\DeadLetterService();

try {
    $deadLetterService->replay(999);
    fwrite(STDERR, 'Replay should have failed for missing item.' . PHP_EOL);
    exit(1);
} catch (RuntimeException $e) {
    if ($e->getMessage() !== 'Dead letter item not found.') {
        fwrite(STDERR, 'Unexpected replay error message.' . PHP_EOL);
        exit(1);
    }
}

echo 'WorkerFailureSmoke OK' . PHP_EOL;