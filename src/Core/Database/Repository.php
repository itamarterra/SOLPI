<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;
use RuntimeException;

/**
 * Base Repository class for all domain repositories
 */
abstract class Repository
{
    protected DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!is_object($DB)) {
            throw new RuntimeException('Conexão com o banco de dados não encontrada.');
        }

        $this->db = $DB;
    }

    /**
     * @param string $query
     * @return mixed
     */
    protected function execute(string $query): mixed
    {
        return $this->db->query($query);
    }

    /**
     * @param array<string,mixed> $data
     * @return string
     */
    protected function escape(array $data): array
    {
        $escaped = [];

        foreach ($data as $key => $value) {
            $escaped[$key] = is_string($value) ? $this->db->real_escape_string($value) : $value;
        }

        return $escaped;
    }
}

