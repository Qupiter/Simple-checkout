<?php

namespace App\Tests\Entity;

use App\Model\BulkPriceRule;
use App\Model\Product;
use PHPUnit\Framework\TestCase;

class BulkPriceRuleTest extends TestCase
{
    public function testBulkPriceRuleInitialization(): void
    {
        $product = $this->createMock(Product::class);
        $bulkPriceRule = new BulkPriceRule(10, 80, $product, true);

        // Test initial state
        $this->assertEquals(10, $bulkPriceRule->getBulkQuantity());
        $this->assertEquals(80, $bulkPriceRule->getBulkPrice());
        $this->assertSame($product, $bulkPriceRule->getProduct());
        $this->assertTrue($bulkPriceRule->isActive());
    }

    public function testSetAndGetBulkQuantity(): void
    {
        $product = $this->createMock(Product::class);
        $bulkPriceRule = new BulkPriceRule(10, 80, $product, true);

        $bulkPriceRule->setBulkQuantity(20);
        $this->assertEquals(20, $bulkPriceRule->getBulkQuantity());
    }

    public function testSetAndGetBulkPrice(): void
    {
        $product = $this->createMock(Product::class);
        $bulkPriceRule = new BulkPriceRule(10, 80, $product, true);

        $bulkPriceRule->setBulkPrice(90);
        $this->assertEquals(90, $bulkPriceRule->getBulkPrice());
    }

    public function testSetAndGetProduct(): void
    {
        $product1 = $this->createMock(Product::class);
        $product2 = $this->createMock(Product::class);
        $bulkPriceRule = new BulkPriceRule(10, 80, $product1, true);

        $bulkPriceRule->setProduct($product2);
        $this->assertSame($product2, $bulkPriceRule->getProduct());
    }

    public function testSetAndIsActive(): void
    {
        $product = $this->createMock(Product::class);
        $bulkPriceRule = new BulkPriceRule(10, 80, $product, true);

        $bulkPriceRule->setIsActive(false);
        $this->assertFalse($bulkPriceRule->isActive());

        $bulkPriceRule->setIsActive(true);
        $this->assertTrue($bulkPriceRule->isActive());
    }

    public function testCalculatePriceWithBulkDiscount(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn(100);

        // Create a BulkPriceRule with a quantity of 10 and bulk price of 800
        $bulkPriceRule = new BulkPriceRule(10, 800, $product, true);

        // Test that for 15 items, it calculates the price correctly:
        // 1 bulk set (10 items) at 80 each = 800
        // 5 remaining items at regular price 100 = 500
        $this->assertEquals(1300, $bulkPriceRule->calculatePrice(15));
    }

    public function testCalculatePriceWithoutBulkDiscount(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn(100);

        // Create a BulkPriceRule with a quantity of 10 and bulk price of 800
        $bulkPriceRule = new BulkPriceRule(10, 800, $product, true);

        // Test that for 5 items, it calculates the price correctly:
        // No bulk discount applies, so regular price for 5 items = 5 * 100 = 500
        $this->assertEquals(500, $bulkPriceRule->calculatePrice(5));
    }

    public function testCalculatePriceMultipleBulkDiscounts(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn(100);

        // Create a BulkPriceRule with a quantity of 10 and bulk price of 800
        $bulkPriceRule = new BulkPriceRule(10, 800, $product, true);

        // Test that for 25 items, it calculates the price correctly:
        // 2 bulk sets (20 items) at 80 each = 1600
        // 5 remaining items at regular price 100 = 500
        $this->assertEquals(2100, $bulkPriceRule->calculatePrice(25));
    }

    public function testSerialize(): void
    {
        $product = $this->createMock(Product::class);
        $bulkPriceRule = new BulkPriceRule(10, 80, $product, true);

        // Test the serialized output
        $serialized = $bulkPriceRule->serialize();
        $expected = [
            'id' => $bulkPriceRule->getId(),
            'bulkQuantity' => 10,
            'bulkPrice' => 80,
        ];

        $this->assertEquals($expected, $serialized);
    }
}
