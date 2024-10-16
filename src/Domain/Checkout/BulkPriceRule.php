<?php

namespace App\Domain\Checkout;

class BulkPriceRule implements PriceRule
{
    public function __construct(
        private readonly Product $item,
        private readonly int     $bulkQuantity,
        private readonly int     $bulkPrice
    ) {}

    public function calculatePrice(int $quantity): int
    {
        // we take the number of BulkRule being repeated in a given quantity
        $bulkCount = intdiv($quantity, $this->bulkQuantity);
        // then we take the surplus
        $regularCount = $quantity % $this->bulkQuantity;
        // total price is the summary between the rule and the regular price
        return ($bulkCount * $this->bulkPrice) + ($regularCount * $this->item->price);
    }

    public function getProduct(): Product
    {
        return $this->item;
    }
}