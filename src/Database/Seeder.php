<?php

declare(strict_types=1);

namespace SOLPI\Database;

use DBmysql;

final class Seeder
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        $this->db = $DB;
    }

    public function seed(): void
    {
        $this->db->insert(

            'glpi_plugin_solpi_versions',

            [

                'version' => '1.0.0',

                'database_version' => '1.0.0',

                'installed_at' => date('Y-m-d H:i:s'),

                'updated_at' => date('Y-m-d H:i:s')

            ]

        );
    }
}
