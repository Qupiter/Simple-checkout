<?php

namespace App\Domain;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

abstract class BaseCollection  implements IteratorAggregate, Countable
{
    protected array $items = [];

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

   abstract public function addItem(mixed $item): void;
}