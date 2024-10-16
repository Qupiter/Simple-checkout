<?php

namespace App\Domain\Checkout;

class Checkout
{
    private ProductCollection $items;
    private RuleCollection $ruleCollection;

    public function __construct(RuleCollection $ruleCollection)
    {
        $this->items = new ProductCollection();
        $this->ruleCollection = $ruleCollection;
    }

    public function scanItem(Product $item): void
    {
        $this->items->addItem($item);
    }

    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->items as $itemData) {
            /** @var Product $item */
            $item = $itemData['item'];
            $quantity = $itemData['quantity'];

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