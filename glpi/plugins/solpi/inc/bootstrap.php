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

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($autoload)) {
    throw new RuntimeException(
        'Composer autoload não encontrado. Execute "composer install".'
    );
}

require_once $autoload;

SOLPI\Core\Bootstrap::initialize();