<?php

namespace App\Repository;

use App\Entity\BulkPriceRule;
use App\Entity\Collections\RuleCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BulkPriceRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BulkPriceRule::class);
    }

    public function findAllActive(): RuleCollection
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.isActive = :active')
            ->setParameter('active', true);

        $rules = $qb->getQuery()->getResult();

        return new RuleCollection($rules);
    }

    public function findAllActiveRulesByProductSku(string $sku): RuleCollection
    {
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.product', 'p')
            ->andWhere('p.sku = :sku')
            ->andWhere('p.isActive = true')
            ->andWhere('b.isActive = true')
            ->setParameter('sku', $sku);

        $rules = $qb->getQuery()->getResult();

        return new RuleCollection($rules);
    }
}