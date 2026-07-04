<?php

declare(strict_types=1);

namespace SOLPI\Database;

use DBmysql;

final class Uninstaller
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        $this->db = $DB;
    }

    public function uninstall(): void
    {
        $tables = [

            'glpi_plugin_solpi_versions',

            'glpi_plugin_solpi_system_logs',

            'glpi_plugin_solpi_settings'

        ];

        foreach ($tables as $table) {

            $this->db->query(

                "DROP TABLE IF EXISTS {$table}"

            );

        }
    }
}
