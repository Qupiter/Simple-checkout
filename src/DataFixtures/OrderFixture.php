<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Enums\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrderFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $orders = [
            [
                'total_price' => 100.00,
                'discount_breakdown' => [
                    ['product' => 'A', 'quantity' => 2, 'regularPrice' => 100.00, 'discountedPrice' => 0, 'appliedRule' => null],
                ],
                'status' => OrderStatus::CREATED,
            ],
            [
                'total_price' => 130.00,
                'discount_breakdown' => [
                    ['product' => 'A', 'quantity' => 3, 'regularPrice' => 150.00, 'discountedPrice' => 130.00, 'appliedRule' => ['bulkQuantity' => 3, 'bulkPrice' => 130]],
                ],
                'status' => OrderStatus::COMPLETED,
            ],
            [
                'total_price' => 110.00,
                'discount_breakdown' => [
                    ['product' => 'A', 'quantity' => 1, 'regularPrice' => 50.00, 'discountedPrice' => 0, 'appliedRule' => null],
                    ['product' => 'B', 'quantity' => 2, 'regularPrice' => 60.00, 'discountedPrice' => 45, 'appliedRule' => ['bulkQuantity' => 2, 'bulkPrice' => 45]],
                ],
                'status' => OrderStatus::CANCELED,
            ],
        ];

        foreach ($orders as $data) {
            $order = new Order($data['total_price'], $data['discount_breakdown']);
            $order->setStatus($data['status']);
            $manager->persist($order);
        }

        $manager->flush();
    }
}
