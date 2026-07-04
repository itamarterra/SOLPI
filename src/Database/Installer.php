<?php

declare(strict_types=1);

namespace SOLPI\Database;

final class Installer
{
    public function install(): void
    {
        $sqlFile = dirname(__DIR__, 2) . '/sql/install.sql';

        if (!is_file($sqlFile)) {
            throw new \RuntimeException(
                'SOLPI: arquivo SQL de instalação não encontrado: ' . $sqlFile
            );
        }

        $sql = file_get_contents($sqlFile);

        if ($sql === false) {
            throw new \RuntimeException(
                'SOLPI: não foi possível ler o arquivo SQL de instalação.'
            );
        }

        $schema = new SchemaManager();
        $schema->execute($sql);
    }

    public function uninstall(): void
    {
        $sqlFile = dirname(__DIR__, 2) . '/sql/uninstall.sql';

        if (!is_file($sqlFile)) {
            return;
        }

        $sql = file_get_contents($sqlFile);

        if ($sql === false) {
            return;
        }

        $schema = new SchemaManager();
        $schema->execute($sql);
    }
}
