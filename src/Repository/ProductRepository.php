<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;


class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param $categoryId
	 * return \Doctrine\ORM\Query
     */
    public function findByCategory($categoryId): array
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.categoryId = :cat')
            ->setParameter('cat', $categoryId)
            ->orderBy('product.rank', 'ASC')
            ->getQuery();

        return $qb->execute();
	}

}