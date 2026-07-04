<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

use DBmysql;
use RuntimeException;

/**
 * SOLPI QueryBuilder - Abstração fluente para o banco de dados do GLPI.
 */
final class QueryBuilder
{
    private DBmysql $db;
    private string $table = '';
    private array $select = ['*'];
    private array $where = [];
    private array $order = [];
    private ?int $limit = null;
    private ?int $offset = null;

    public function __construct(?DBmysql $db = null)
    {
        if ($db === null) {
            global $DB;
            if (!$DB instanceof DBmysql) {
                throw new RuntimeException("Conexão com o banco de dados não disponível.");
            }
            $db = $DB;
        }
        $this->db = $db;
    }

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(array $criteria): self
    {
        $this->where = array_merge($this->where, $criteria);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->order[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * Constrói o array compatível com o $DB->request() do GLPI.
     */
    public function build(): array
    {
        if (empty($this->table)) {
            throw new RuntimeException("Tabela não especificada no QueryBuilder.");
        }

        $query = [
            'SELECT' => $this->select,
            'FROM'   => $this->table
        ];

        if (!empty($this->where)) {
            $query['WHERE'] = $this->where;
        }

        if (!empty($this->order)) {
            $query['ORDER'] = $this->order;
        }

        if ($this->limit !== null) {
            $query['LIMIT'] = $this->limit;
            if ($this->offset !== null) {
                $query['OFFSET'] = $this->offset;
            }
        }

        return $query;
    }

    /**
     * Executa a query e retorna os resultados.
     */
    public function execute(): array
    {
        $results = [];
        foreach ($this->db->request($this->build()) as $row) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Retorna apenas o primeiro resultado.
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->execute();
        return $results[0] ?? null;
    }
}

