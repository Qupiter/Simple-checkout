<?php

namespace App\Domain\Checkout;

class Product
{
    public function __construct(public string $sku, public int $price) {}
}