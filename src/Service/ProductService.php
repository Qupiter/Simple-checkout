<?php

namespace App\Service;

use App\Entity\Collections\ProductCollection;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository      $productRepository,
        private readonly BulkPriceRuleService $bulkPriceRuleService
    ) {}

    public function getAllProducts(): ProductCollection
    {
        return $this->productRepository->findAllActive();
    }

    public function findOneBySku(string $sku): ?Product
    {
        return $this->productRepository->findBySku($sku);
    }

    public function findActiveBySku(string $sku): ?Product
    {
        return $this->productRepository->findOneBy(['sku' => $sku, 'isActive' => true]);
    }


    public function createProduct(string $sku, int $price): Product
    {
        // Disable all existing products with the same SKU
        $this->disableProductsWithSameSku($sku);

        // Create and persist the new product
        $product = new Product($sku, $price);
        $product->setCreatedAtValue();
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function updateProduct(Product $product, int $price): Product
    {
        // Disable all previous prices for this SKU
        $this->disableProductsWithSameSku($product->getSku());

        // Update product's price and set it as active
        $product->setPrice($price);
        $product->setIsActive(true);

        $product->setUpdatedAtValue();
        $this->entityManager->flush();
        return $product;
    }

    public function disableProduct(Product $product): void
    {
        // Disable any associated rules before disabling the product
        $this->bulkPriceRuleService->disableActiveRules($product);

        // Disable the product instead of deleting
        $product->setIsActive(false);
        $this->entityManager->flush();
    }

    private function disableProductsWithSameSku(string $sku): void
    {
        $products = $this->productRepository->findBy(['sku' => $sku, 'isActive' => true]);
        foreach ($products as $product) {
            $product->setIsActive(false);
        }
        $this->entityManager->flush();
    }
}