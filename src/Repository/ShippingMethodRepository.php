<?php

namespace App\Repository;

use App\Entity\ShippingMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ShippingMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingMethod::class);
    }

    /**
     */
    public function findAllOrdered()
     {
         $qb = $this->createQueryBuilder('s')
             ->orderBy('s.ordering', 'ASC')
             ->getQuery();

         return $qb->execute();
     }

}