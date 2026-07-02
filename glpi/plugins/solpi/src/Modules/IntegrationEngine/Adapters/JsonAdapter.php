<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class JsonAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        $data = $payload['data'] ?? null;

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (!is_array($decoded)) {
                throw new RuntimeException('JSON adapter received invalid JSON string.');
            }
            $data = $decoded;
        }

        if (!is_array($data)) {
            throw new RuntimeException('JSON adapter requires array data.');
        }

        $records = isset($data[0]) ? $data : [$data];

        return [
            'records' => $records,
            'meta' => [
                'count' => count($records),
                'adapter' => 'json',
            ],
        ];
    }
}
