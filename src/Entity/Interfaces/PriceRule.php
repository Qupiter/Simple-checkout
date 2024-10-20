<?php

namespace App\Entity\Interfaces;

use App\Entity\Product;

interface PriceRule
{
    public function calculatePrice(int $quantity): int;
    public function getProduct(): Product;
}