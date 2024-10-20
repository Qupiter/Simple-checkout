<?php

namespace App\Service\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;
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

    public function addItems(array $items): void
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function mergeCollections(BaseCollection $collection): void
    {
        foreach ($collection as $itemData) {
            $this->addItem($itemData);
        }
    }

    public function clear(): void
    {
        $this->items = [];
    }
}