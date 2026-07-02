<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class CsvAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        $content = (string)($payload['content'] ?? '');
        $path = (string)($payload['path'] ?? '');

        if ($content === '' && $path !== '') {
            if (!is_file($path)) {
                throw new RuntimeException('CSV adapter file not found: ' . $path);
            }
            $content = (string)file_get_contents($path);
        }

        if ($content === '') {
            throw new RuntimeException('CSV adapter requires content or path.');
        }

        $delimiter = (string)($payload['delimiter'] ?? ',');
        $lines = preg_split('/\r\n|\n|\r/', trim($content)) ?: [];
        if ($lines === []) {
            return ['records' => [], 'meta' => ['count' => 0]];
        }

        $headers = str_getcsv((string)array_shift($lines), $delimiter);
        $records = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line, $delimiter);
            $row = [];
            foreach ($headers as $i => $h) {
                $row[(string)$h] = $values[$i] ?? null;
            }
            $records[] = $row;
        }

        return [
            'records' => $records,
            'meta' => [
                'count' => count($records),
                'adapter' => 'csv',
            ],
        ];
    }
}
