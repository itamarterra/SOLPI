<?php

// Export of modified file: src/Core/Collection.php

declare(strict_types=1);

namespace SOLPI\Core;

use Countable;
use IteratorAggregate;
use ArrayIterator;

final class Collection implements Countable, IteratorAggregate
{
    /**
     * Internal items storage.
     *
     * @var array<int,mixed>
     */
    private array $items = [];

    public function add(
        mixed $item
    ): void{

        $this->items[]=$item;

    }

    /**
     * Return all items as an indexed array.
     *
     * @return array<int,mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return ArrayIterator<int,mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(
            $this->items
        );
    }
}
