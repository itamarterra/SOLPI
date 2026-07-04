<?php

declare(strict_types=1);

require_once __DIR__ . '/../Adapters/SourceAdapterInterface.php';
require_once __DIR__ . '/../Adapters/RestApiAdapter.php';
require_once __DIR__ . '/../Adapters/SoapAdapter.php';

use SOLPI\Modules\IntegrationEngine\Adapters\RestApiAdapter;
use SOLPI\Modules\IntegrationEngine\Adapters\SoapAdapter;

$rest = new RestApiAdapter();

try {
    $rest->ingest([]);
    fwrite(STDERR, 'REST validation failed: expected exception for missing url.' . PHP_EOL);
    exit(1);
} catch (RuntimeException $e) {
    if (strpos($e->getMessage(), 'requires url') === false) {
        fwrite(STDERR, 'REST validation failed: unexpected message: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}

$soap = new SoapAdapter();

try {
    $soap->ingest([]);
    fwrite(STDERR, 'SOAP validation failed: expected exception.' . PHP_EOL);
    exit(1);
} catch (RuntimeException $e) {
    $message = $e->getMessage();
    $isExpected = strpos($message, 'SOAP extension not available') !== false
        || strpos($message, 'requires wsdl and operation') !== false;

    if (!$isExpected) {
        fwrite(STDERR, 'SOAP validation failed: unexpected message: ' . $message . PHP_EOL);
        exit(1);
    }
}

echo 'RestSoapAdapterSmoke OK' . PHP_EOL;
