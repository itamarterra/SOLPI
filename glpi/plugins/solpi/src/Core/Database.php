<?php

declare(strict_types=1);

namespace SOLPI\Core;

use DBmysql;
use RuntimeException;

final class Database
{
    private ?DBmysql $connection = null;

    public function connect(): DBmysql
    {
        if ($this->connection instanceof DBmysql) {
            return $this->connection;
        }

        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException(
                'Não foi possível obter a conexão do GLPI.'
            );
        }

        $this->connection = $DB;

        return $this->connection;
    }

    public function connection(): DBmysql
    {
        return $this->connect();
    }

    public function request(array $query): iterable
    {
        return $this->connect()->request($query);
    }

    public function query(string $sql): mixed
    {
        return $this->connect()->query($sql);
    }

    public function escape(string $value): string
    {
        return $this->connect()->escape($value);
    }

    public function insertId(): int
    {
        return (int)$this->connect()->insertId();
    }

    public function begin(): void
    {
        $this->query('START TRANSACTION');
    }

    public function commit(): void
    {
        $this->query('COMMIT');
    }

    public function rollback(): void
    {
        $this->query('ROLLBACK');
    }

    public function isConnected(): bool
    {
        return $this->connection instanceof DBmysql;
    }
}