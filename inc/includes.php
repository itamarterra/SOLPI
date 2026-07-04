<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
}

if (!defined('GLPI_ROOT') || !file_exists(GLPI_ROOT . 'inc/includes.php')) {
    exit;
}

require_once GLPI_ROOT . 'inc/includes.php';
