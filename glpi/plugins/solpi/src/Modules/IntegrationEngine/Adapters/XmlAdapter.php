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

        $records = isset($decoded[0]) ? $decoded : [$decoded];

        return [
            'records' => $records,
            'meta' => [
                'count' => count($records),
                'adapter' => 'xml',
            ],
        ];
    }
}
