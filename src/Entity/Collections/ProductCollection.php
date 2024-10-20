<?php

namespace App\Entity\Collections;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

class ProductCollection extends ArrayCollection
{
    public function addItem(mixed $element): void
    {
        if (!$element instanceof Product) {
            throw new InvalidArgumentException('Expected an instance of' . Product::class);
        }

        $this->add($element);
    }

    public function getBySku(string $sku): ?Product
    {
        return $this->filter(fn(Product $product) => $product->getSku() === $sku)->first() ?: null;
    }

    public function hasSku(string $sku): bool
    {
        return $this->exists(fn(int $key, Product $product) => $product->getSku() === $sku);
    }

    public function hasAllSkus(array $skus): bool
    {
        foreach ($skus as $sku) {
            if (!$this->hasSku($sku)) {
                return false;
            }
        }

        return true;
    }
}