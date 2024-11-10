<?php

namespace App\DataFixtures;

use App\Model\BulkPriceRule;
use App\Model\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BulkPriceRuleFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Reference existing products by their SKU
        $productA = $manager->getRepository(Product::class)->findOneBy(['sku' => 'A']);
        $productB = $manager->getRepository(Product::class)->findOneBy(['sku' => 'B']);

        $rules = [
            ['bulk_quantity' => 3, 'bulk_price' => 130, 'product' => $productA, 'isActive' => true],
            ['bulk_quantity' => 3, 'bulk_price' => 120, 'product' => $productA, 'isActive' => false],
            ['bulk_quantity' => 2, 'bulk_price' => 45, 'product' => $productB, 'isActive' => true],
        ];

        foreach ($rules as $data) {
            /** @var Product $product */
            $product = $data['product'];

            $rule = new BulkPriceRule(
                $data['bulk_quantity'],
                $data['bulk_price'],
                $product,
                $data['isActive']
            );

            $rule->setCreatedAtValue();
            $rule->setUpdatedAtValue();

            $manager->persist($rule);
        }

        $manager->flush();
    }
}
