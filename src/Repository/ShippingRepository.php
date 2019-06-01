<?php

namespace App\Repository;

use App\Entity\Shipping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class ShippingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Shipping::class);
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