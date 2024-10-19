<?php

namespace App\Entity;

use InvalidArgumentException;

class ProductCollection extends BaseCollection
{
    public function addItem(mixed $item): void
    {
        if (!$item instanceof Product) {
            throw new InvalidArgumentException('Expected an instance of' . Product::class);
        }

        $hash = spl_object_hash($item);
        if (!isset($this->items[$hash])) {
            $this->items[$hash] = ['item' => $item, 'quantity' => 0];
        }
        $this->items[$hash]['quantity']++;
    }

    public function mergeCollections(BaseCollection $collection): void
    {
        if (!$collection instanceof ProductCollection) {
            throw new InvalidArgumentException('Expected an instance of' . ProductCollection::class);
        }

        foreach ($collection as $itemData) {
            $item = $itemData['item'];
            $this->addItem($item);
        }
    }
}