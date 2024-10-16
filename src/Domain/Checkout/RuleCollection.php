<?php

namespace App\Domain\Checkout;

use App\Domain\BaseCollection;
use InvalidArgumentException;

class RuleCollection extends BaseCollection
{
    public function addItem(mixed $item): void
    {
        if (!$item instanceof BulkPriceRule) {
            throw new InvalidArgumentException('Expected an instance of' . BulkPriceRule::class);
        }

        $this->items[] = $item;
    }

    public function findRuleForProduct(Product $product): ?BulkPriceRule
    {
        foreach ($this->items as $rule) {
            if ($rule->getProduct() === $product) {
                return $rule;
            }
        }
        return null;
    }
}