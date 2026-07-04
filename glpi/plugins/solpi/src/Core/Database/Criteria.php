<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

final class Criteria
{
    private array $conditions = [];

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->conditions[] = [
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value
        ];
        return $this;
    }

    public function toArray(): array
    {
        $criteria = [];
        foreach ($this->conditions as $cond) {
            $key = $cond['column'];
            if ($cond['operator'] !== '=') {
                $key .= ' ' . $cond['operator'];
            }
            $criteria[$key] = $cond['value'];
        }
        return $criteria;
    }
}

