<?php

namespace App\Collections;

use App\Model\BulkPriceRule;
use App\Model\Product;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

class RuleCollection extends ArrayCollection
{
    public function addItem(mixed $element): void
    {
        if (!$element instanceof BulkPriceRule) {
            throw new InvalidArgumentException('Expected an instance of' . BulkPriceRule::class);
        }

        $this->add($element);
    }

    public function addItems(array $items): void
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function findRuleForProduct(Product $product): ?BulkPriceRule
    {
        foreach ($this->toArray() as $rule) {
            if ($rule->getProduct() === $product) {
                return $rule;
            }
        }
        return null;
    }
}