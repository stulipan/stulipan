<?php

namespace App\Repository;

use App\Entity\Inventory\InventoryProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class InventoryProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryProduct::class);
    }


    /**
     * @ var $category
     * @return InventoryProduct[]
     */
    public function findAllProductsAndOrderByCategory()
     {
         $qb = $this->createQueryBuilder('p')
             ->orderBy('p.category', 'ASC')
             ->getQuery();

         return $qb->execute();
     }

}