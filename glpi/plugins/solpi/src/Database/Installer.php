<?php

declare(strict_types=1);

namespace SOLPI\Database;

final class Installer
{
    public function install(): void
    {
        $schema = new SchemaManager();

        $sql = file_get_contents(

            __DIR__ . '/../../database/install.sql'

        );

        $schema->execute($sql);
    }
}
