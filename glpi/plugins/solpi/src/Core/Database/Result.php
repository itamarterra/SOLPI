<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

final class Result implements \IteratorAggregate
{
    private array $rows;

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->rows);
    }

    public function count(): int
    {
        return count($this->rows);
    }

    public function fetch(): ?array
    {
        return array_shift($this->rows);
    }

    public function all(): array
    {
        return $this->rows;
    }
}

