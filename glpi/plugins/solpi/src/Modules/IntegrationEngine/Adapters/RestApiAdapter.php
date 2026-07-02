<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class RestApiAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL extension not available in runtime.');
        }

        $url = (string)($payload['url'] ?? '');
        if ($url === '') {
            throw new RuntimeException('REST adapter requires url.');
        }

        $method = strtoupper((string)($payload['method'] ?? 'GET'));
        $headers = $payload['headers'] ?? [];
        $body = $payload['body'] ?? null;

        $pagination = is_array($payload['pagination'] ?? null) ? $payload['pagination'] : [];
        $enabledPagination = (bool)($pagination['enabled'] ?? false);

        $records = [];
        $pagesFetched = 0;
        $lastStatus = 0;
        $mode = strtolower((string)($pagination['mode'] ?? 'page'));
        $lastCursor = '';

        if (!$enabledPagination) {
            $single = $this->request($url, $method, $headers, $body, (int)($payload['timeout'] ?? 30));
            $lastStatus = (int)$single['status'];
            $records = $this->extractRecords($single['response'], $payload);
            $pagesFetched = 1;
        } else {
            $maxPages = max(1, (int)($pagination['max_pages'] ?? 50));
            $perPage = max(1, (int)($pagination['per_page'] ?? 100));
            $page = max(1, (int)($pagination['start_page'] ?? 1));
            $offset = max(0, (int)($pagination['start_offset'] ?? 0));
            $cursor = (string)($pagination['start_cursor'] ?? '');
            $paramPage = (string)($pagination['param_page'] ?? 'page');
            $paramLimit = (string)($pagination['param_limit'] ?? 'per_page');
            $paramOffset = (string)($pagination['param_offset'] ?? 'offset');
            $paramCursor = (string)($pagination['param_cursor'] ?? 'cursor');
            $cursorField = (string)($pagination['cursor_field'] ?? 'next_cursor');
            $stopWhenEmpty = (bool)($pagination['stop_when_empty'] ?? true);

            for ($i = 0; $i < $maxPages; $i++) {
                $query = [];
                if ($mode === 'offset') {
                    $query[$paramOffset] = $offset;
                    $query[$paramLimit] = $perPage;
                } elseif ($mode === 'cursor') {
                    if ($cursor !== '') {
                        $query[$paramCursor] = $cursor;
                    }
                    $query[$paramLimit] = $perPage;
                } else {
                    $query[$paramPage] = $page;
                    $query[$paramLimit] = $perPage;
                }

                $requestUrl = $this->appendQuery($url, $query);
                $batch = $this->request($requestUrl, $method, $headers, $body, (int)($payload['timeout'] ?? 30));
                $lastStatus = (int)$batch['status'];
                $batchRecords = $this->extractRecords($batch['response'], $payload);

                $pagesFetched++;
                foreach ($batchRecords as $item) {
                    $records[] = $item;
                }

                if ($batchRecords === [] && $stopWhenEmpty) {
                    break;
                }

                if ($mode === 'offset') {
                    $offset += $perPage;
                } elseif ($mode === 'cursor') {
                    $decoded = json_decode($batch['response'], true);
                    $next = is_array($decoded) ? (string)($decoded[$cursorField] ?? '') : '';
                    if ($next === '' || $next === $cursor) {
                        break;
                    }
                    $cursor = $next;
                    $lastCursor = $cursor;
                } else {
                    $page++;
                }
            }
        }

        return [
            'records' => $records,
            'meta' => [
                'http_status' => $lastStatus,
                'url' => $url,
                'method' => $method,
                'count' => count($records),
                'pages_fetched' => $pagesFetched,
                'pagination_enabled' => $enabledPagination,
                'pagination_mode' => $mode,
                'last_cursor' => $lastCursor,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $headers
     * @return array{response:string,status:int}
     */
    private function request(string $url, string $method, array $headers, mixed $body, int $timeout): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        if ($headers !== []) {
            $normalized = [];
            foreach ($headers as $k => $v) {
                if (is_string($k)) {
                    $normalized[] = $k . ': ' . (string)$v;
                    continue;
                }
                $normalized[] = (string)$v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $normalized);
        }

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $encoded = is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        }

        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('REST adapter request failed: ' . $error);
        }

        return [
            'response' => $response,
            'status' => $status,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<int,array<string,mixed>>
     */
    private function extractRecords(string $response, array $payload): array
    {
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return [[
                'raw' => $response,
            ]];
        }

        $recordsPath = (string)($payload['records_path'] ?? '');
        $data = $recordsPath !== '' ? $this->valueByPath($decoded, $recordsPath) : $decoded;

        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            return $data;
        }

        if (is_array($data) && isset($data[0]) && !is_array($data[0])) {
            $rows = [];
            foreach ($data as $value) {
                $rows[] = ['value' => $value];
            }
            return $rows;
        }

        if (is_array($data)) {
            return [$data];
        }

        return [[
            'value' => $data,
        ]];
    }

    /**
     * @param array<string,mixed> $query
     */
    private function appendQuery(string $url, array $query): string
    {
        if ($query === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . http_build_query($query);
    }

    private function valueByPath(array $data, string $path): mixed
    {
        $current = $data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return [];
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
