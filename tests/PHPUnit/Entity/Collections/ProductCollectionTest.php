<?php

namespace App\Tests\Entity\Collections;

use App\Collections\ProductCollection;
use App\Model\Product;
use PHPUnit\Framework\TestCase;

class ProductCollectionTest extends TestCase
{
    private ProductCollection $productCollection;

    protected function setUp(): void
    {
        $this->productCollection = new ProductCollection();
    }

    public function testAddItem(): void
    {
        $product = new Product('ABC123', 100);
        $this->productCollection->addItem($product);

        $this->assertSame($product, $this->productCollection->first());
    }

    public function testAddItemInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of App\Entity\Product');

        $this->productCollection->addItem('invalid');
    }

    public function testGetBySku(): void
    {
        $product1 = new Product('ABC123', 100);
        $product2 = new Product('DEF456', 150);

        $this->productCollection->addItem($product1);
        $this->productCollection->addItem($product2);

        $this->assertSame($product1, $this->productCollection->getBySku('ABC123'));
        $this->assertNull($this->productCollection->getBySku('XYZ999')); // non-existent SKU
    }

    public function testHasSku(): void
    {
        $product = new Product('ABC123', 100);
        $this->productCollection->addItem($product);

        $this->assertTrue($this->productCollection->hasSku('ABC123'));
        $this->assertFalse($this->productCollection->hasSku('XYZ999')); // non-existent SKU
    }

    public function testHasAllSkus(): void
    {
        $product1 = new Product('ABC123', 100);
        $product2 = new Product('DEF456', 150);

        $this->productCollection->addItem($product1);
        $this->productCollection->addItem($product2);

        $this->assertTrue($this->productCollection->hasAllSkus(['ABC123', 'DEF456']));
        $this->assertFalse($this->productCollection->hasAllSkus(['ABC123', 'XYZ999'])); // one non-existent SKU
    }
}
