<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use PDO;
use PDOException;
use RuntimeException;

final class SqlAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        $dsn = (string)($payload['dsn'] ?? '');
        $user = (string)($payload['user'] ?? '');
        $password = (string)($payload['password'] ?? '');
        $query = (string)($payload['query'] ?? '');
        $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];

        if ($dsn === '' || $query === '') {
            throw new RuntimeException('SQL adapter requires dsn and query.');
        }

        try {
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pagination = is_array($payload['pagination'] ?? null) ? $payload['pagination'] : [];
            $incremental = is_array($payload['incremental'] ?? null) ? $payload['incremental'] : [];
            $incrementalColumn = (string)($incremental['column'] ?? '');

            $records = [];
            $pagesFetched = 0;
            $workingQuery = $this->applyIncremental($query, $incremental, $params);

            $enabledPagination = (bool)($pagination['enabled'] ?? false);
            if (!$enabledPagination) {
                $stmt = $pdo->prepare($workingQuery);
                foreach ($params as $key => $value) {
                    $stmt->bindValue((string)$key, $value);
                }
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $records = $rows;
                $pagesFetched = 1;
            } else {
                $pageSize = max(1, (int)($pagination['page_size'] ?? 500));
                $maxPages = max(1, (int)($pagination['max_pages'] ?? 100));
                $offset = max(0, (int)($pagination['start_offset'] ?? 0));
                $stopWhenEmpty = (bool)($pagination['stop_when_empty'] ?? true);
                $stopWhenShortPage = (bool)($pagination['stop_when_short_page'] ?? true);

                for ($i = 0; $i < $maxPages; $i++) {
                    $pagedQuery = $this->ensurePaginationClause($workingQuery);
                    $stmt = $pdo->prepare($pagedQuery);

                    foreach ($params as $key => $value) {
                        $stmt->bindValue((string)$key, $value);
                    }

                    $stmt->bindValue(':__solpi_limit', $pageSize, PDO::PARAM_INT);
                    $stmt->bindValue(':__solpi_offset', $offset, PDO::PARAM_INT);

                    $stmt->execute();
                    $batch = $stmt->fetchAll();
                    $batchRecords = $batch;

                    $pagesFetched++;
                    foreach ($batchRecords as $row) {
                        $records[] = $row;
                    }

                    if ($batchRecords === [] && $stopWhenEmpty) {
                        break;
                    }

                    if ($stopWhenShortPage && count($batchRecords) < $pageSize) {
                        break;
                    }

                    $offset += $pageSize;
                }
            }

            $maxIncrementalValue = $this->extractMaxIncrementalValue($records, $incrementalColumn);

            return [
                'records' => $records,
                'meta' => [
                    'adapter' => 'sql',
                    'count' => count($records),
                    'pagination_enabled' => $enabledPagination,
                    'pages_fetched' => $pagesFetched,
                    'stop_when_short_page' => $enabledPagination ? $stopWhenShortPage : null,
                    'incremental_column' => $incrementalColumn,
                    'max_incremental_value' => $maxIncrementalValue,
                ],
            ];
        } catch (PDOException $e) {
            throw new RuntimeException('SQL adapter failed: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string,mixed> $incremental
     * @param array<string,mixed> $params
     */
    private function applyIncremental(string $query, array $incremental, array &$params): string
    {
        $enabled = (bool)($incremental['enabled'] ?? false);
        if (!$enabled) {
            return $query;
        }

        $column = (string)($incremental['column'] ?? '');
        $value = $incremental['value'] ?? null;
        $operator = strtoupper((string)($incremental['operator'] ?? '>'));
        $allowedOps = ['>', '>=', '<', '<=', '='];

        if ($column === '' || !in_array($operator, $allowedOps, true)) {
            return $query;
        }

        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $column)) {
            throw new RuntimeException('SQL adapter incremental column is invalid.');
        }

        $placeholder = ':__solpi_incremental_value';
        $params[$placeholder] = $value;

        return 'SELECT * FROM (' . $query . ') AS solpi_src WHERE ' . $column . ' ' . $operator . ' ' . $placeholder;
    }

    private function ensurePaginationClause(string $query): string
    {
        if (stripos($query, ':__solpi_limit') !== false && stripos($query, ':__solpi_offset') !== false) {
            return $query;
        }

        return rtrim($query, " \t\n\r\0\x0B;") . ' LIMIT :__solpi_limit OFFSET :__solpi_offset';
    }

    /**
     * @param array<int,array<string,mixed>> $records
     */
    private function extractMaxIncrementalValue(array $records, string $column): mixed
    {
        if ($column === '' || $records === []) {
            return null;
        }

        $simpleColumn = explode('.', $column);
        $columnKey = (string)end($simpleColumn);
        if ($columnKey === '') {
            return null;
        }

        $max = null;
        foreach ($records as $row) {
            if (!is_array($row) || !array_key_exists($columnKey, $row)) {
                continue;
            }

            $value = $row[$columnKey];
            if ($value === null || $value === '') {
                continue;
            }

            if ($max === null || (string)$value > (string)$max) {
                $max = $value;
            }
        }

        return $max;
    }
}
