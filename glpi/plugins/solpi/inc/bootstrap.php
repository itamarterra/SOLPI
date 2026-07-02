<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SOLPI Professional
|--------------------------------------------------------------------------
| Bootstrap Loader
|--------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
    exit;
}

if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    throw new RuntimeException(
        'SOLPI requer PHP 8.3.0 ou superior. Versao atual: ' . PHP_VERSION
    );
}

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($autoload)) {
    throw new RuntimeException(
        'Composer autoload não encontrado. Execute "composer install".'
    );
}

require_once $autoload;

SOLPI\Core\Bootstrap::initialize();