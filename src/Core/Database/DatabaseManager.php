<?php

declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;

final class DatabaseManager
{
    private static ?self $instance = null;
    private Connection $connection;

    private function __construct()
    {
        $this->connection = new Connection();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function table(string $table): QueryBuilder
    {
        $qb = new QueryBuilder($this->connection->getNativeConnection());
        return $qb->from($table);
    }

    public function raw(string $sql): mixed
    {
        return $this->connection->query($sql);
    }
}
