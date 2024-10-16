<?php

namespace App\Domain\Checkout;

interface PriceRule
{
    public function calculatePrice(int $quantity): int;
    public function getProduct(): Product;
}