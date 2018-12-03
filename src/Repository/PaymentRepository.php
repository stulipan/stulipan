<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     */
    public function findAllOrdered()
     {
         $qb = $this->createQueryBuilder('p')
             ->orderBy('p.order', 'ASC')
             ->getQuery();

         return $qb->execute();
     }

}