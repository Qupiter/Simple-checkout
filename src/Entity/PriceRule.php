<?php

namespace App\Entity;

use App\Domain\Checkout\Product;

interface PriceRule
{
    public function calculatePrice(int $quantity): int;
    public function getProduct(): Product;
}