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

        $records = is_array($decoded)
            ? (isset($decoded[0]) ? $decoded : [$decoded])
            : [['value' => $result]];

        return [
            'records' => $records,
            'meta' => [
                'adapter' => 'soap',
                'operation' => $operation,
            ],
        ];
    }
}
