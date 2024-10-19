<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCollection;
use App\Entity\RuleCollection;

class CheckoutService
{
    private ProductCollection $productCollection;
    private RuleCollection $ruleCollection;

    public function __construct(RuleCollection $ruleCollection)
    {
        $this->productCollection = new ProductCollection();
        $this->ruleCollection = $ruleCollection;
    }

    public function scanProduct(Product $product): void
    {
        $this->productCollection->addItem($product);
    }

    public function scanCollection(ProductCollection $productCollection): void
    {
        $this->productCollection->mergeCollections($productCollection);
    }

    public function clearCart(): void
    {
        $this->productCollection->clear();
    }

    public function getTotal(): int
    {
        $total = 0;

        /** @var Product $productData */
        foreach ($this->productCollection as $productData) {
            $item = $productData['item'];
            $quantity = $productData['quantity'];

            // Find the matching pricing rule for the item
            $rule = $this->ruleCollection->findRuleForProduct($item);

            if ($rule) {
                // Apply bulk pricing rule
                $total += $rule->calculatePrice($quantity);
            } else {
                // Default pricing (no bulk rule)
                $total += $item->price * $quantity;
            }
        }

        return $total;
    }
}