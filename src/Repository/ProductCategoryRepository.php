<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Product\ProductCategory;
use Symfony\Bridge\Doctrine\RegistryInterface;


class ProductCategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductCategory::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */	
    public function findAllOrdered()
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC')
            ->getQuery();
    
        return $qb->execute();
	}

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC');
    }

}