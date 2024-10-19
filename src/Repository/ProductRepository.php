<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.sku = :sku')
            ->setParameter('sku', $sku)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}