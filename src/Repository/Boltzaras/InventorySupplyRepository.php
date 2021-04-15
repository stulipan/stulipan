<?php

namespace App\Repository\Boltzaras;

use App\Entity\Boltzaras\InventorySupply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class InventorySupplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventorySupply::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
         return $this->createQueryBuilder('s')
             ->orderBy('s.datum', 'DESC');
    }

}