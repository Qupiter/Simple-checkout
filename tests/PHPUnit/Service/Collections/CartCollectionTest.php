<?php

namespace App\Tests\Service\Collections;

use App\Collections\BaseCollection;
use App\Collections\CartCollection;
use App\Model\Product;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DummyCollection extends BaseCollection
{
    public function addItem(mixed $item): void
    {
        // TODO: Implement addItem() method.
    }
}

class CartCollectionTest extends TestCase
{
    private CartCollection $cart;

    protected function setUp(): void
    {
        $this->cart = new CartCollection();
    }

    public function testAddValidProduct(): void
    {
        $product = new Product('SKU1', 100);

        $this->cart->addItem($product);

        $this->assertCount(1, $this->cart);
        $this->assertSame($product, $this->cart->getBySku($product->getSku()));
    }

    public function testAddDuplicateProduct(): void
    {
        $product = new Product('SKU1', 100);

        $this->cart->addItem($product);
        $this->cart->addItem($product);

        $this->assertCount(1, $this->cart);
        $this->assertSame($product, $this->cart->getBySku($product->getSku()));
    }

    public function testAddInvalidItem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of App\Entity\Product');

        $this->cart->addItem('invalid item'); // Adding a non-Product item
    }

    public function testMergeCollections(): void
    {
        $product1 = new Product('SKU2', 150);
        $product2 = new Product('SKU3', 200);

        $this->cart->addItem($product1);

        $otherCart = new CartCollection();
        $otherCart->addItem($product2);

        $this->cart->mergeCollections($otherCart);

        $this->assertCount(2, $this->cart);
        $this->assertSame($product2, $this->cart->getBySku($product2->getSku()));
    }

    public function testMergeInvalidCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of App\Service\Collections\CartCollection');

        $nonCartCollection = new DummyCollection();
        $this->cart->mergeCollections($nonCartCollection);
    }
}
