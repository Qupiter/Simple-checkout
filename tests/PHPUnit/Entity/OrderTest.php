<?php

namespace App\Tests\Entity;

use App\Model\Order;
use App\Model\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testOrderInitialization(): void
    {
        $discountBreakdown = ['rule1' => 100, 'rule2' => 50];
        $order = new Order(1000, $discountBreakdown);

        // Test initial state
        $this->assertEquals(1000, $order->getTotalPrice());
        $this->assertSame($discountBreakdown, $order->getDiscountBreakdown());
        $this->assertEquals(OrderStatus::CREATED, $order->getStatus());
    }

    public function testSetAndGetTotalPrice(): void
    {
        $discountBreakdown = ['rule1' => 100, 'rule2' => 50];
        $order = new Order(1000, $discountBreakdown);

        $order->setTotalPrice(1200);
        $this->assertEquals(1200, $order->getTotalPrice());
    }

    public function testSetAndGetDiscountBreakdown(): void
    {
        $discountBreakdown = ['rule1' => 100, 'rule2' => 50];
        $order = new Order(1000, $discountBreakdown);

        $newBreakdown = ['rule3' => 30, 'rule4' => 20];
        $order->setDiscountBreakdown($newBreakdown);
        $this->assertSame($newBreakdown, $order->getDiscountBreakdown());
    }

    public function testSetAndGetStatus(): void
    {
        $discountBreakdown = ['rule1' => 100, 'rule2' => 50];
        $order = new Order(1000, $discountBreakdown);

        $order->setStatus(OrderStatus::COMPLETED);
        $this->assertEquals(OrderStatus::COMPLETED, $order->getStatus());

        $order->setStatus(OrderStatus::CANCELED);
        $this->assertEquals(OrderStatus::CANCELED, $order->getStatus());
    }

    public function testSerialize(): void
    {
        $discountBreakdown = ['rule1' => 100, 'rule2' => 50];
        $order = new Order(1000, $discountBreakdown);
        $order->setStatus(OrderStatus::COMPLETED);

        $serialized = $order->serialize();

        $expected = [
            'id' => $order->getId(),
            'status' => 'COMPLETED',
            'totalPrice' => 1000,
            'discountBreakdown' => ['rule1' => 100, 'rule2' => 50],
        ];

        $this->assertEquals($expected, $serialized);
    }
}