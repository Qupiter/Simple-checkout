<?php

namespace App\Service\Collections;

use App\Entity\Product;
use InvalidArgumentException;

class CartCollection extends BaseCollection
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
        if (!$collection instanceof CartCollection) {
            throw new InvalidArgumentException('Expected an instance of' . CartCollection::class);
        }

        foreach ($collection as $itemData) {
            $item = $itemData['item'];
            $this->addItem($item);
        }
    }
}