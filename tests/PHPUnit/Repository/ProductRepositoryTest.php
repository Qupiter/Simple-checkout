<?php

namespace App\Tests\Repository;

use App\Entity\Product;
use App\Entity\Collections\ProductCollection;
use App\Tests\BaseTestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;

class ProductRepositoryTest extends BaseTestCase
{
    private ?EntityManager $entityManager;
    private ?ObjectRepository $productRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Initialize the product repository
        $this->productRepository = $this->entityManager->getRepository(Product::class);
    }

    public function testFindBySku(): void
    {
        // Assume that the necessary fixtures have been loaded or created in your database.
        $product = $this->productRepository->findBySku('A');

        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('A', $product->getSku()); // Adjust the SKU as per your fixtures
    }

    public function testFindAllActive(): void
    {
        // Assume that the necessary fixtures have been loaded or created in your database.
        $products = $this->productRepository->findAllActive();

        $this->assertInstanceOf(ProductCollection::class, $products);
        // Adjust the count as per your fixtures
        $this->assertGreaterThan(0, $products->count());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Closing the entity manager to avoid memory leaks
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
