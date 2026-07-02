<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class SoapAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        if (!class_exists('SoapClient')) {
            throw new RuntimeException('SOAP extension not available in runtime.');
        }

        $wsdl = (string)($payload['wsdl'] ?? '');
        $operation = (string)($payload['operation'] ?? '');
        $params = $payload['params'] ?? [];

        if ($wsdl === '' || $operation === '') {
            throw new RuntimeException('SOAP adapter requires wsdl and operation.');
        }

        $client = new \SoapClient($wsdl, [
            'trace' => (bool)($payload['trace'] ?? false),
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $result = $client->__soapCall($operation, [is_array($params) ? $params : []]);

        $encoded = json_encode($result, JSON_UNESCAPED_UNICODE);
        $decoded = json_decode((string)$encoded, true);
        $recordsPath = (string)($payload['records_path'] ?? '');
        $data = $recordsPath !== '' ? $this->valueByPath(is_array($decoded) ? $decoded : [], $recordsPath) : $decoded;

        $records = is_array($data)
            ? $this->normalizeRecords($data)
            : [['value' => $data]];

        return [
            'records' => $records,
            'meta' => [
                'adapter' => 'soap',
                'operation' => $operation,
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
