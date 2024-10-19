<?php

namespace App\Service;

use App\Entity\BulkPriceRule;
use App\Entity\Product;
use App\Repository\BulkPriceRuleRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class BulkPriceRuleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BulkPriceRuleRepository $bulkPriceRuleRepository,
        private readonly ProductRepository $productRepository
    ) {}

    public function getAllBulkPriceRules(): array
    {
        return $this->bulkPriceRuleRepository->findAllActive();
    }

    public function createRule(Product $product, int $bulkQuantity, int $bulkPrice): BulkPriceRule
    {
        // Disable existing active rules for this product
        $this->disableActiveRules($product);

        $rule = new BulkPriceRule($bulkQuantity, $bulkPrice, $product);
        $this->entityManager->persist($rule);
        $this->entityManager->flush();

        return $rule;
    }

    /**
     * @param string $sku
     * @param array $data
     * @return BulkPriceRule|null
     * @throws \Exception
     */
    public function updateRule(string $sku, array $data): ?BulkPriceRule
    {
        $product = $this->productRepository->findBySku($sku);

        if (!$product) {
            throw new \Exception("Product with SKU '{$sku}' not found.");
        }

        return $this->createRule($product, $data['bulk_quantity'], $data['bulk_price']);
    }

    /**
     * @param string $sku
     * @return void
     * @throws \Exception
     */
    public function disableRulesBySku(string $sku): void
    {
        $product = $this->productRepository->findBySku($sku);

        if (!$product) {
            throw new \Exception("Product with SKU '{$sku}' not found.");
        }

        $this->disableActiveRules($product);
    }

    public function disableActiveRules(Product $product): void
    {
        $activeRules = $this->bulkPriceRuleRepository->findAllActiveRulesByProductSku($product->getSku());
        foreach ($activeRules as $activeRule) {
            $activeRule->setIsActive(false);
        }
        $this->entityManager->flush();
    }
}