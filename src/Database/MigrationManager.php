<?php

declare(strict_types=1);

namespace SOLPI\Database;

final class MigrationManager
{
    private array $migrations = [];

    public function add(
        Migration $migration
    ): void {

        $this->migrations[] = $migration;

    }

    public function migrate(): void
    {
        foreach ($this->migrations as $migration) {

            $migration->up();

        }
    }

    public function rollback(): void
    {
        foreach (array_reverse($this->migrations) as $migration) {

            $migration->down();

        }
    }
}
