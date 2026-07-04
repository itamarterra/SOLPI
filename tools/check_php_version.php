<?php

declare(strict_types=1);

$minimumVersion = $argv[1] ?? '8.3.0';
$currentVersion = PHP_VERSION;

if (version_compare($currentVersion, $minimumVersion, '<')) {
    fwrite(STDERR, sprintf(
        'SOLPI requer PHP %s ou superior. Versao atual: %s' . PHP_EOL,
        $minimumVersion,
        $currentVersion
    ));

    exit(1);
}

echo sprintf(
    'PHP version check OK: %s >= %s' . PHP_EOL,
    $currentVersion,
    $minimumVersion
);