<?php

namespace App\Repository;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class PaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }

    /**
     */
    public function findAllOrdered()
     {
         $qb = $this->createQueryBuilder('p')
             ->andWhere('p.enabled = :enabled')
             ->setParameter('enabled', 1)
             ->orderBy('p.ordering', 'ASC')
             ->getQuery();

         return $qb->execute();
     }

}