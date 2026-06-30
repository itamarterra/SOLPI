<?php

declare(strict_types=1);

namespace SOLPI\Database;

use DBmysql;
use RuntimeException;

final class DatabaseManager
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException(
                'Conexão com o banco do GLPI não encontrada.'
            );
        }

        $this->db = $DB;
    }

    public function query(string $sql): bool
    {
        return (bool)$this->db->query($sql);
    }

    public function beginTransaction(): void
    {
        $this->db->query('START TRANSACTION');
    }

    public function commit(): void
    {
        $this->db->query('COMMIT');
    }

    public function rollback(): void
    {
        $this->db->query('ROLLBACK');
    }

    public function tableExists(
        string $table
    ): bool {

        $result = $this->db->query(

            "SHOW TABLES LIKE '{$table}'"

        );

        return $this->db->numrows($result) > 0;

    }

    public function databaseVersion(): ?string
    {
        if (!$this->tableExists('glpi_plugin_solpi_versions')) {
            return null;
        }

        foreach (

            $this->db->request([

                'FROM'=>'glpi_plugin_solpi_versions',

                'ORDER'=>'id DESC',

                'LIMIT'=>1

            ])

            as $row

        ){

            return $row['database_version'];

        }

        return null;

    }
}
