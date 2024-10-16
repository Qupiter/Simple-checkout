<?php

namespace App\Tests\Domain\Checkout;

use App\Domain\Checkout\BulkPriceRule;
use App\Domain\Checkout\Checkout;
use App\Domain\Checkout\Product;
use App\Domain\Checkout\RuleCollection;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    private Product $productA;
    private Product $productB;
    private Product $productC;
    private Product $productD;
    private Checkout $checkout;

    protected function setUp(): void
    {
        // Initialize products
        $this->productA = new Product('A', 50);
        $this->productB = new Product('B', 30);
        $this->productC = new Product('C', 20);
        $this->productD = new Product('D', 10);

        // Create the rule collection and add bulk pricing rules
        $ruleCollection = new RuleCollection();
        $ruleCollection->addItems([
            new BulkPriceRule($this->productA, 3, 130), // Buy 3 A's for 130
            new BulkPriceRule($this->productB, 2, 45),  // Buy 2 B's for 45
        ]);

        // Initialize checkout with the rule collection
        $this->checkout = new Checkout($ruleCollection);
    }

    public function testGetTotalForSingleA(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(50, $this->checkout->getTotal());
    }

    public function testGetTotalForAB(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productB);
        $this->assertEquals(80, $this->checkout->getTotal()); // 50 + 30
    }

    public function testGetTotalForCDBA(): void
    {
        $this->checkout->scanProduct($this->productC);
        $this->checkout->scanProduct($this->productD);
        $this->checkout->scanProduct($this->productB);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(110, $this->checkout->getTotal()); // 20 + 10 + 30 + 50
    }

    public function testGetTotalForAA(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(100, $this->checkout->getTotal()); // 50 + 50
    }

    public function testGetTotalForAAA(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(130, $this->checkout->getTotal()); // Bulk price applies
    }

    public function testGetTotalForAAAA(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(180, $this->checkout->getTotal()); // 130 + 50
    }

    public function testGetTotalForAAAAA(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(230, $this->checkout->getTotal()); // 130 + 100
    }

    public function testGetTotalForAAAAAA(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(260, $this->checkout->getTotal()); // 130 + 130
    }

    public function testGetTotalForAAAB(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productB);
        $this->assertEquals(160, $this->checkout->getTotal()); // 130 + 30
    }

    public function testGetTotalForAAABB(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productB);
        $this->checkout->scanProduct($this->productB);
        $this->assertEquals(175, $this->checkout->getTotal()); // 130 + 45
    }

    public function testGetTotalForAAABBD(): void
    {
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productB);
        $this->checkout->scanProduct($this->productB);
        $this->checkout->scanProduct($this->productD);
        $this->assertEquals(185, $this->checkout->getTotal()); // 130 + 45 + 10
    }

    public function testGetTotalForDABABA(): void
    {
        $this->checkout->scanProduct($this->productD);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productB);
        $this->checkout->scanProduct($this->productA);
        $this->checkout->scanProduct($this->productB);
        $this->checkout->scanProduct($this->productA);
        $this->assertEquals(185, $this->checkout->getTotal()); // 10 + 130 + 45
    }
}
