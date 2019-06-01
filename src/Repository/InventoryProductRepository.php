<?php

namespace App\Repository;

use App\Entity\Inventory\InventoryProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class InventoryProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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