<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    exit;
}

/**
 * Instala as tabelas do plugin SOLPI.
 *
 * Executa o arquivo sql/install.sql via SchemaManager.
 * O SQL usa CREATE TABLE IF NOT EXISTS, tornando a operação idempotente.
 */
function plugin_solpi_install(): bool
{
    require_once __DIR__ . '/bootstrap.php';

    $installer = new SOLPI\Database\Installer();
    $installer->install();

    return true;
}