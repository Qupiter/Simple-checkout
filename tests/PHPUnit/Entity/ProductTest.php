<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use App\Entity\BulkPriceRule;
use Doctrine\Common\Collections\ArrayCollection;

class ProductTest extends TestCase
{
    public function testProductInitialization(): void
    {
        $product = new Product('A', 100, true);

        // Test initial state
        $this->assertEquals('A', $product->getSku());
        $this->assertEquals(100, $product->getPrice());
        $this->assertTrue($product->isActive());
        $this->assertInstanceOf(ArrayCollection::class, $product->getBulkPriceRules());
        $this->assertCount(0, $product->getBulkPriceRules());
    }

    public function testSetAndGetSku(): void
    {
        $product = new Product('A', 100, true);
        $product->setSku('B');
        $this->assertEquals('B', $product->getSku());
    }

    public function testSetAndGetPrice(): void
    {
        $product = new Product('A', 100, true);
        $product->setPrice(200);
        $this->assertEquals(200, $product->getPrice());
    }

    public function testSetAndIsActive(): void
    {
        $product = new Product('A', 100, true);
        $product->setIsActive(false);
        $this->assertFalse($product->isActive());

        $product->setIsActive(true);
        $this->assertTrue($product->isActive());
    }

    public function testSetAndGetBulkPriceRules(): void
    {
        $product = new Product('A', 100, true);
        $bulkPriceRule1 = $this->createMock(BulkPriceRule::class);
        $bulkPriceRule2 = $this->createMock(BulkPriceRule::class);

        // Test adding bulk price rules
        $bulkPriceRules = new ArrayCollection([$bulkPriceRule1, $bulkPriceRule2]);
        $product->setBulkPriceRules($bulkPriceRules);

        $this->assertCount(2, $product->getBulkPriceRules());
        $this->assertSame($bulkPriceRule1, $product->getBulkPriceRules()->first());
        $this->assertSame($bulkPriceRule2, $product->getBulkPriceRules()->last());
    }

    public function testSerializeWithoutBulkPriceRules(): void
    {
        $product = new Product('A', 100, true);

        $serialized = $product->serialize();
        $expected = [
            'sku' => 'A',
            'price' => 100,
        ];

        $this->assertEquals($expected, $serialized);
    }

    public function testSerializeWithActiveBulkPriceRules(): void
    {
        $product = new Product('A', 100, true);

        // Mock active bulk price rule
        $bulkPriceRule = $this->createMock(BulkPriceRule::class);
        $bulkPriceRule->expects($this->once())
            ->method('isActive')
            ->willReturn(true);

        $bulkPriceRule->expects($this->once())
            ->method('serialize')
            ->willReturn([
                'bulk_quantity' => 10,
                'bulk_price' => 80,
            ]);

        $bulkPriceRules = new ArrayCollection([$bulkPriceRule]);
        $product->setBulkPriceRules($bulkPriceRules);

        $serialized = $product->serialize();
        $expected = [
            'sku' => 'A',
            'price' => 100,
            'bulkPriceRules' => [
                'bulk_quantity' => 10,
                'bulk_price' => 80,
            ],
        ];

        $this->assertEquals($expected, $serialized);
    }

    public function testSerializeWithoutActiveBulkPriceRules(): void
    {
        $product = new Product('A', 100, true);

        // Mock inactive bulk price rule
        $bulkPriceRule = $this->createMock(BulkPriceRule::class);
        $bulkPriceRule->expects($this->once())
            ->method('isActive')
            ->willReturn(false);

        $bulkPriceRules = new ArrayCollection([$bulkPriceRule]);
        $product->setBulkPriceRules($bulkPriceRules);

        $serialized = $product->serialize();
        $expected = [
            'sku' => 'A',
            'price' => 100,
        ];

        $this->assertEquals($expected, $serialized);
    }
}
