<?php

namespace App\Repository;

use App\Entity\DeliverySpecialDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class DeliverySpecialDateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeliverySpecialDate::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
         return $this->createQueryBuilder('d')
             ->orderBy('d.specialDate', 'DESC')
             ;
    }

}