<?php

namespace App\Service\Collections;

use App\Entity\Product;
use InvalidArgumentException;

class CartCollection extends BaseCollection
{
    public function getBySku(string $sku): ?Product
    {
        foreach ($this->items as $itemData) {
            /** @var Product $item */
            $item = $itemData['item'];
            if ($item->getSku() === $sku) {
                return $item;
            }
        }

        return null;
    }

    public function addItem(mixed $item): void
    {
        if (!$item instanceof Product) {
            throw new InvalidArgumentException('Expected an instance of ' . Product::class);
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
            throw new InvalidArgumentException('Expected an instance of ' . CartCollection::class);
        }

        foreach ($collection as $itemData) {
            $item = $itemData['item'];
            $quantity = $itemData['quantity'];

            while ($quantity > 0) {
                $this->addItem($item);
                $quantity--;
            }
        }
    }
}