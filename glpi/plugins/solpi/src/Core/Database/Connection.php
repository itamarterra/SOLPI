<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;
use RuntimeException;

final class Connection
{
    private DBmysql $db;

    public function __construct(?DBmysql $db = null)
    {
        if ($db === null) {
            global $DB;
            if (!$DB instanceof DBmysql) {
                throw new RuntimeException("Conexão com o banco de dados não disponível no GLPI.");
            }
            $db = $DB;
        }
        $this->db = $db;
    }

    public function getRaw(): DBmysql
    {
        return $this->db;
    }

    public function query(string $query): mixed
    {
        return $this->db->query($query);
    }
}

