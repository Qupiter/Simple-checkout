<?php

namespace App\Tests\Entity\Collections;

use App\Collections\RuleCollection;
use App\Model\BulkPriceRule;
use App\Model\Product;
use PHPUnit\Framework\TestCase;

class RuleCollectionTest extends TestCase
{
    private RuleCollection $ruleCollection;

    protected function setUp(): void
    {
        $this->ruleCollection = new RuleCollection();
    }

    public function testAddItem(): void
    {
        $product = new Product('ABC123', 100);
        $rule = new BulkPriceRule(2, 180, $product);

        $this->ruleCollection->addItem($rule);

        $this->assertSame($rule, $this->ruleCollection->first());
    }

    public function testAddItemInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of' . BulkPriceRule::class);

        $this->ruleCollection->addItem('invalid');
    }

    public function testAddItems(): void
    {
        $product1 = new Product('ABC123', 100);
        $rule1 = new BulkPriceRule(2, 180, $product1);
        $product2 = new Product('DEF456', 150);
        $rule2 = new BulkPriceRule(3, 400, $product2);

        $this->ruleCollection->addItems([$rule1, $rule2]);

        $this->assertCount(2, $this->ruleCollection);
        $this->assertSame($rule1, $this->ruleCollection->get(0));
        $this->assertSame($rule2, $this->ruleCollection->get(1));
    }

    public function testFindRuleForProduct(): void
    {
        $product1 = new Product('ABC123', 100);
        $rule1 = new BulkPriceRule(2, 180, $product1);
        $product2 = new Product('DEF456', 150);
        $rule2 = new BulkPriceRule(3, 400, $product2);

        $this->ruleCollection->addItems([$rule1, $rule2]);

        $this->assertSame($rule1, $this->ruleCollection->findRuleForProduct($product1));
        $this->assertSame($rule2, $this->ruleCollection->findRuleForProduct($product2));
        $this->assertNull($this->ruleCollection->findRuleForProduct(new Product('XYZ999', 200))); // Non-existent product
    }
}