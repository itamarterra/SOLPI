<?php

declare(strict_types=1);

use Migration;

if (!defined('GLPI_ROOT')) {
    exit;
}

/**
 * Instala o plugin SOLPI.
 */
function plugin_solpi_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_SOLPI_VERSION);

    /*
     * As tabelas serão adicionadas aqui nas próximas Sprints.
     */

    $migration->executeMigration();

    return true;
}