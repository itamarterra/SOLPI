<?php

declare(strict_types=1);

namespace SOLPI\Database;

use DBmysql;

final class SchemaManager
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        $this->db = $DB;
    }

    public function execute(string $sql): void
    {
        $commands = preg_split(
            '/;\s*[\r\n]+/',
            $sql
        );

        foreach ($commands as $command) {

            $command = trim($command);

            if ($command === '') {
                continue;
            }

            $this->db->query($command);

        }
    }
}
