<?php

declare(strict_types=1);

namespace SOLPI\Core\Database;

use IteratorAggregate;
use ArrayIterator;
use Traversable;

/**
 * Wrapper for database query results
 */
final class Result implements IteratorAggregate
{
    private array $data;

    public function __construct(iterable $data)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->data = iterator_to_array($data);
        }
    }

    public function all(): array
    {
        return $this->data;
    }

    public function first(): ?array
    {
        return $this->data[0] ?? null;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
