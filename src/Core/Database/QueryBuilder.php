<?php

declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;
use RuntimeException;

/**
 * Fluent Query Builder for GLPI Database
 */
final class QueryBuilder
{
    private DBmysql $db;
    private array $query = [];

    public function __construct(?DBmysql $db = null)
    {
        if ($db === null) {
            global $DB;
            if (!$DB instanceof DBmysql) {
                throw new RuntimeException('Conexão com o banco de dados não disponível.');
            }
            $db = $DB;
        }
        $this->db = $db;
    }

    public function select(array|string $fields): self
    {
        $this->query['SELECT'] = (array)$fields;
        return $this;
    }

    public function from(string $table): self
    {
        $this->query['FROM'] = $table;
        return $this;
    }

    public function where(array $criteria): self
    {
        $this->query['WHERE'] = $criteria;
        return $this;
    }

    public function order(string|array $order): self
    {
        $this->query['ORDER'] = $order;
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->query['LIMIT'] = $limit;
        if ($offset > 0) {
            $this->query['OFFSET'] = $offset;
        }
        return $this;
    }

    public function join(string $table, array $on, string $type = 'LEFT JOIN'): self
    {
        if (!isset($this->query['JOIN'])) {
            $this->query['JOIN'] = [];
        }
        $this->query['JOIN'][$table] = [
            'ON' => $on,
            'TYPE' => $type
        ];
        return $this;
    }

    /**
     * @return iterable<array<string,mixed>>
     */
    public function get(): iterable
    {
        return $this->db->request($this->query);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function first(): ?array
    {
        $q = $this->query;
        $q['LIMIT'] = 1;

        $it = $this->db->request($q);
        foreach ($it as $row) {
            return $row;
        }
        return null;
    }

    public function count(): int
    {
        $q = $this->query;
        $q['COUNT'] = 'total';

        // Remove SELECT para não conflitar com o COUNT do GLPI
        unset($q['SELECT']);

        $it = $this->db->request($q);
        foreach ($it as $row) {
            return (int)($row['total'] ?? 0);
        }
        return 0;
    }

    public function delete(): bool
    {
        return $this->db->delete($this->query['FROM'], $this->query['WHERE'] ?? []);
    }

    public function toArray(): array
    {
        return $this->query;
    }

    public function execute(): mixed
    {
        return $this->db->request($this->query);
    }
}
