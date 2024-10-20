<?php

namespace App\Service;

use App\Entity\BulkPriceRule;
use App\Entity\Collections\RuleCollection;
use App\Entity\Product;
use App\Repository\BulkPriceRuleRepository;
use Doctrine\ORM\EntityManagerInterface;

class BulkPriceRuleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BulkPriceRuleRepository $bulkPriceRuleRepository
    ) {}

    public function getAllBulkPriceRules(): RuleCollection
    {
        return $this->bulkPriceRuleRepository->findAllActive();
    }

    public function createRule(Product $product, int $bulkQuantity, int $bulkPrice): BulkPriceRule
    {
        // Disable existing active rules for this product
        $this->disableActiveRules($product);

        $rule = new BulkPriceRule($bulkQuantity, $bulkPrice, $product);
        $rule->setCreatedAtValue();
        $this->entityManager->persist($rule);
        $this->entityManager->flush();

        return $rule;
    }

    public function updateRule(Product $product, int $bulkQuantity, int $bulkPrice): BulkPriceRule
    {
        return $this->createRule($product, $bulkQuantity, $bulkPrice);
    }

    public function disableRulesBySku(Product $product): void
    {
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