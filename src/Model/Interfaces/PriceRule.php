<?php

namespace App\Model\Interfaces;

use App\Model\Product;

interface PriceRule
{
    public function calculatePrice(int $quantity): int;
    public function getProduct(): Product;
}