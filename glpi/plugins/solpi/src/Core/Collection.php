<?php

declare(strict_types=1);

namespace SOLPI\Core;

use Countable;
use IteratorAggregate;
use ArrayIterator;

final class Collection implements Countable, IteratorAggregate
{
    private array $items=[];

    public function add(
        mixed $item
    ): void{

        $this->items[]=$item;

    }

    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(
            $this->items
        );
    }
}
