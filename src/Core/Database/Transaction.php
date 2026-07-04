<?php

declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;

final class Transaction
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;
        $this->db = $DB;
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

    /**
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function wrap(callable $callback): mixed
    {
        $this->begin();
        try {
            $result = $callback($this->db);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
