<?php

namespace App\Repository;

use App\Entity\DeliveryDateType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class DeliveryDateTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeliveryDateType::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
         return $this->createQueryBuilder('d')
//             ->orderBy('d.date', 'DESC')
             ;
    }

}