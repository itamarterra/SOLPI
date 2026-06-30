<?php

declare(strict_types=1);

use SOLPI\Database\Uninstaller;

function plugin_solpi_uninstall(): bool
{
    try {

        $uninstaller = new Uninstaller();

        $uninstaller->uninstall();

        return true;

    } catch (Throwable $e) {

        Toolbox::logError($e->getMessage());

        return false;

    }
}
