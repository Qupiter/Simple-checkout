<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['sku' => 'A', 'price' => 50, 'isActive' => true],
            ['sku' => 'B', 'price' => 30, 'isActive' => true],
            ['sku' => 'C', 'price' => 20, 'isActive' => true],
            ['sku' => 'D', 'price' => 10, 'isActive' => true],
        ];

        foreach ($products as $data) {
            $product = new Product(
                $data['sku'],
                $data['price'],
                $data['isActive']
            );
            $manager->persist($product);
        }

        $manager->flush();
    }
}
