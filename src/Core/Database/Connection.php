<?php

declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;
use RuntimeException;

final class Connection
{
    private ?DBmysql $db = null;

    public function __construct()
    {
        global $DB;
        if ($DB instanceof DBmysql) {
            $this->db = $DB;
        }
    }

    public function getNativeConnection(): DBmysql
    {
        if ($this->db === null) {
            throw new RuntimeException('Conexão com o GLPI não inicializada.');
        }
        return $this->db;
    }

    public function query(string $sql): mixed
    {
        return $this->getNativeConnection()->query($sql);
    }

    public function request(array $query): iterable
    {
        return $this->getNativeConnection()->request($query);
    }

    public function insert(string $table, array $params): bool|int
    {
        if ($this->getNativeConnection()->insert($table, $params)) {
            return (int)$this->getNativeConnection()->insertId();
        }
        return false;
    }

    public function update(string $table, array $params, array $where): bool
    {
        return $this->getNativeConnection()->update($table, $params, $where);
    }

    public function delete(string $table, array $where): bool
    {
        return $this->getNativeConnection()->delete($table, $where);
    }

    public function escape(string $value): string
    {
        return $this->getNativeConnection()->escape($value);
    }
}
