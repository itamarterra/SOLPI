<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;

final class Transaction
{
    private DBmysql $db;

    public function __construct(DBmysql $db)
    {
        $this->db = $db;
    }

    public function begin(): void
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
}

