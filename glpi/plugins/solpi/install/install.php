<?php

declare(strict_types=1);

use SOLPI\Database\Installer;
use SOLPI\Database\Seeder;

function plugin_solpi_install(): bool
{
    try {

        $installer = new Installer();

        $installer->install();

        $seeder = new Seeder();

        $seeder->seed();

        return true;

    } catch (Throwable $e) {

        Toolbox::logError($e->getMessage());

        return false;

    }
}
