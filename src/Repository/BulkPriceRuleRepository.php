<?php

namespace App\Repository;

use App\Entity\BulkPriceRule;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BulkPriceRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BulkPriceRule::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    public function findAllActiveRulesByProductSku(string $sku): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.product', 'p')
            ->andWhere('p.sku = :sku')
            ->andWhere('p.isActive = true')
            ->andWhere('b.isActive = true')
            ->setParameter('sku', $sku)
            ->getQuery()
            ->getResult();
    }
}