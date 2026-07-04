<?php

declare(strict_types=1);

require_once __DIR__ . '/../Adapters/SourceAdapterInterface.php';
require_once __DIR__ . '/../Adapters/CsvAdapter.php';
require_once __DIR__ . '/../Adapters/XmlAdapter.php';

use SOLPI\Modules\IntegrationEngine\Adapters\CsvAdapter;
use SOLPI\Modules\IntegrationEngine\Adapters\XmlAdapter;

$csvPath = sys_get_temp_dir() . '/solpi_csv_adapter_smoke_' . uniqid('', true) . '.csv';
$xmlPath = sys_get_temp_dir() . '/solpi_xml_adapter_smoke_' . uniqid('', true) . '.xml';

$cleanup = static function () use ($csvPath, $xmlPath): void {
    if (is_file($csvPath)) {
        @unlink($csvPath);
    }

    if (is_file($xmlPath)) {
        @unlink($xmlPath);
    }
};
register_shutdown_function($cleanup);

file_put_contents($csvPath, implode(PHP_EOL, [
    'id,name,email',
    '1,Ana,ana@example.test',
    '2,Bruno,bruno@example.test',
    '3,Carla,carla@example.test',
]));

file_put_contents($xmlPath, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<response>
  <items>
    <item>
      <id>10</id>
      <name>Alpha</name>
    </item>
    <item>
      <id>20</id>
      <name>Beta</name>
    </item>
  </items>
</response>
XML);

$csvAdapter = new CsvAdapter();
$csvResult = $csvAdapter->ingest([
    'path' => $csvPath,
    'offset' => 1,
    'limit' => 1,
]);

$xmlAdapter = new XmlAdapter();
$xmlResult = $xmlAdapter->ingest([
    'path' => $xmlPath,
    'records_path' => 'items.item',
]);

if (($csvResult['meta']['count'] ?? null) !== 1) {
    fwrite(STDERR, 'CSV count is invalid.' . PHP_EOL);
    exit(1);
}

if (($csvResult['meta']['offset'] ?? null) !== 1 || ($csvResult['meta']['limit'] ?? null) !== 1) {
    fwrite(STDERR, 'CSV pagination metadata is invalid.' . PHP_EOL);
    exit(1);
}

if (($csvResult['records'][0]['name'] ?? null) !== 'Bruno') {
    fwrite(STDERR, 'CSV selected row is invalid.' . PHP_EOL);
    exit(1);
}

if (($xmlResult['meta']['count'] ?? null) !== 2) {
    fwrite(STDERR, 'XML count is invalid.' . PHP_EOL);
    exit(1);
}

if (($xmlResult['meta']['records_path'] ?? null) !== 'items.item') {
    fwrite(STDERR, 'XML records_path metadata is invalid.' . PHP_EOL);
    exit(1);
}

if (($xmlResult['records'][1]['name'] ?? null) !== 'Beta') {
    fwrite(STDERR, 'XML selected row is invalid.' . PHP_EOL);
    exit(1);
}

echo 'CsvXmlAdapterSmoke OK' . PHP_EOL;