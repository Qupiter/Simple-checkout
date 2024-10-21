<?php

namespace App\Tests\Service;

use App\Entity\BulkPriceRule;
use App\Entity\Product;
use App\Entity\Collections\RuleCollection;
use App\Repository\BulkPriceRuleRepository;
use App\Service\BulkPriceRuleService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BulkPriceRuleServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private BulkPriceRuleRepository $bulkPriceRuleRepository;
    private BulkPriceRuleService $bulkPriceRuleService;

    protected function setUp(): void
    {
        // Mock the EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock the BulkPriceRuleRepository
        $this->bulkPriceRuleRepository = $this->createMock(BulkPriceRuleRepository::class);

        // Instantiate the service with mocked dependencies
        $this->bulkPriceRuleService = new BulkPriceRuleService(
            $this->entityManager,
            $this->bulkPriceRuleRepository
        );
    }

    public function testGetAllBulkPriceRules(): void
    {
        $ruleCollection = new RuleCollection();
        $this->bulkPriceRuleRepository->method('findAllActive')->willReturn($ruleCollection);

        $result = $this->bulkPriceRuleService->getAllBulkPriceRules();

        $this->assertSame($ruleCollection, $result);
    }

    public function testCreateRuleDisablesExistingActiveRules(): void
    {
        $product = new Product('ABC123', 100);

        $activeRule = new BulkPriceRule(2, 180, $product);
        $activeRule->setIsActive(true);

        $this->bulkPriceRuleRepository
            ->method('findAllActiveRulesByProductSku')
            ->with($product->getSku())
            ->willReturn(new RuleCollection([$activeRule]));

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->atLeast(2))->method('flush');

        $rule = $this->bulkPriceRuleService->createRule($product, 3, 150);

        $this->assertInstanceOf(BulkPriceRule::class, $rule);
        $this->assertSame(3, $rule->getBulkQuantity());
        $this->assertSame(150, $rule->getBulkPrice());
        $this->assertFalse($activeRule->isActive());
    }

    public function testUpdateRule(): void
    {
        $product = new Product('ABC123', 100);

        $this->bulkPriceRuleRepository
            ->method('findAllActiveRulesByProductSku')
            ->willReturn(new RuleCollection([])); // No existing rules

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->atLeast(2))->method('flush');

        $rule = $this->bulkPriceRuleService->updateRule($product, 5, 200);

        $this->assertInstanceOf(BulkPriceRule::class, $rule);
        $this->assertSame(5, $rule->getBulkQuantity());
        $this->assertSame(200, $rule->getBulkPrice());
    }

    public function testDisableActiveRules(): void
    {
        $product = new Product('ABC123', 100);

        $activeRule = new BulkPriceRule(2, 180, $product);
        $activeRule->setIsActive(true);

        $this->bulkPriceRuleRepository
            ->method('findAllActiveRulesByProductSku')
            ->willReturn(new RuleCollection([$activeRule]));

        $this->entityManager->expects($this->once())->method('flush');

        $this->bulkPriceRuleService->disableActiveRules($product);

        $this->assertFalse($activeRule->isActive());
    }
}
