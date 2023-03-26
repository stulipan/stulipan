<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Product\ProductCategory;
use Doctrine\Persistence\ManagerRegistry;


class ProductCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCategory::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */	
    public function findAllOrdered()
    {
        $qb = $this->createQueryBuilder('o')
            ->orderBy('o.name', 'ASC')
            ->getQuery();
    
        return $qb->execute();
	}

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.name', 'ASC');
    }

//    /**
//     * @return\Doctrine\ORM\Query
//     */
//    public function findAllEnabled()
//    {
//        $qb = $this->createQueryBuilder('o')
//            ->orderBy('o.name', 'ASC')
//            ->getQuery();
//
//        return $qb->execute();
//    }

}