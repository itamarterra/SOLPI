<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Matchers;

final class AssetMatcher implements MatcherInterface
{
    private KeyNormalizer $normalizer;

    public function __construct()
    {
        $this->normalizer = new KeyNormalizer();
    }

    public function match(array $record): array
    {
        $keys = [];

        $uuid = $this->normalizer->text((string)($record['uuid'] ?? ''));
        if ($uuid !== '') {
            $keys[] = ['type' => 'uuid', 'value' => $uuid, 'weight' => 1.00];
        }

        $serial = $this->normalizer->text((string)($record['serial'] ?? ''));
        if ($serial !== '') {
            $keys[] = ['type' => 'serial', 'value' => $serial, 'weight' => 0.99];
        }

        $assetTag = $this->normalizer->text((string)($record['asset_tag'] ?? $record['tag'] ?? ''));
        if ($assetTag !== '') {
            $keys[] = ['type' => 'asset_tag', 'value' => $assetTag, 'weight' => 0.98];
        }

        $hostname = $this->normalizer->text((string)($record['hostname'] ?? $record['name'] ?? ''));
        if ($hostname !== '') {
            $keys[] = ['type' => 'hostname', 'value' => $hostname, 'weight' => 0.90];
        }

        $mac = $this->normalizer->text((string)($record['mac'] ?? $record['mac_address'] ?? ''));
        if ($mac !== '') {
            $keys[] = ['type' => 'mac', 'value' => $mac, 'weight' => 0.94];
        }

        $patrimony = $this->normalizer->text((string)($record['patrimony'] ?? ''));
        if ($patrimony !== '') {
            $keys[] = ['type' => 'patrimony', 'value' => $patrimony, 'weight' => 0.92];
        }

        $model = $this->normalizer->text((string)($record['model'] ?? ''));
        $manufacturer = $this->normalizer->text((string)($record['manufacturer'] ?? ''));
        if ($model !== '' && $manufacturer !== '') {
            $keys[] = ['type' => 'model_manufacturer', 'value' => $model . '|' . $manufacturer, 'weight' => 0.75];
        }

        return [
            'entity_type' => 'asset',
            'keys' => $keys,
        ];
    }
}
