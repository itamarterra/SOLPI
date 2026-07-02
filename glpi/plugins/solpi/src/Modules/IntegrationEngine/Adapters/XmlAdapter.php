<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class XmlAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        $xml = (string)($payload['xml'] ?? '');
        $path = (string)($payload['path'] ?? '');

        if ($xml === '' && $path !== '') {
            if (!is_file($path)) {
                throw new RuntimeException('XML adapter file not found: ' . $path);
            }
            $xml = (string)file_get_contents($path);
        }

        if ($xml === '') {
            throw new RuntimeException('XML adapter requires xml or path.');
        }

        libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($root === false) {
            throw new RuntimeException('XML adapter failed to parse XML.');
        }

        $json = json_encode($root, JSON_UNESCAPED_UNICODE);
        $decoded = json_decode((string)$json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('XML adapter could not decode parsed XML.');
        }

        $recordsPath = (string)($payload['records_path'] ?? '');
        $data = $recordsPath !== '' ? $this->valueByPath($decoded, $recordsPath) : $decoded;
        $records = $this->normalizeRecords($data);

        return [
            'records' => $records,
            'meta' => [
                'count' => count($records),
                'adapter' => 'xml',
                'records_path' => $recordsPath !== '' ? $recordsPath : null,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @return array<int,array<string,mixed>>
     */
    private function normalizeRecords(array $data): array
    {
        if (isset($data[0]) && is_array($data[0])) {
            return $data;
        }

        if (isset($data[0]) && !is_array($data[0])) {
            $rows = [];
            foreach ($data as $value) {
                $rows[] = ['value' => $value];
            }

            return $rows;
        }

        return [$data];
    }

    /**
     * @param array<string,mixed> $data
     */
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
