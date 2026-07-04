<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    exit;
}

/**
 * Remove todas as tabelas do plugin SOLPI.
 *
 * Executa o arquivo sql/uninstall.sql via SchemaManager.
 */
function plugin_solpi_uninstall(): bool
{
    require_once __DIR__ . '/bootstrap.php';

    $installer = new SOLPI\Database\Installer();
    $installer->uninstall();

    return true;
}