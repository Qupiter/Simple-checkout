<?php

namespace App\Tests\Repository;

use App\Collections\RuleCollection;
use App\Model\BulkPriceRule;
use App\Tests\BaseTestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;

class BulkPriceRuleRepositoryTest extends BaseTestCase
{
    private ?EntityManager $entityManager;
    private ?ObjectRepository $bulkPriceRuleRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Load fixtures or initialize the repository here if needed
        $this->bulkPriceRuleRepository = $this->entityManager->getRepository(BulkPriceRule::class);
    }

    public function testFindAllActive(): void
    {
        // Assume that the necessary fixtures have been loaded or created in your database.
        $rules = $this->bulkPriceRuleRepository->findAllActive();

        $this->assertInstanceOf(RuleCollection::class, $rules);
        $this->assertCount(2, $rules);
    }

    public function testFindAllActiveRulesByProductSku(): void
    {
        // Assume that the necessary fixtures have been loaded or created in your database.
        $rules = $this->bulkPriceRuleRepository->findAllActiveRulesByProductSku('ABC123');

        $this->assertInstanceOf(RuleCollection::class, $rules);
        $this->assertCount(0, $rules);
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
