<?php

declare(strict_types=1);

namespace SOLPI\Core\Database;

/**
 * Helper to build GLPI-style WHERE arrays
 */
final class Criteria
{
    private array $where = [];

    public function where(string $column, mixed $value, string $operator = '='): self
    {
        if ($operator === '=') {
            $this->where[$column] = $value;
        } else {
            $this->where[$column] = [$operator, $value];
        }
        return $this;
    }

    public function and(array $conditions): self
    {
        $this->where['AND'] = array_merge($this->where['AND'] ?? [], $conditions);
        return $this;
    }

    public function or(array $conditions): self
    {
        $this->where['OR'] = array_merge($this->where['OR'] ?? [], $conditions);
        return $this;
    }

    public function toArray(): array
    {
        return $this->where;
    }
}
