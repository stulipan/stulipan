<?php

namespace App\Repository;

use App\Entity\Inventory\InventoryWaste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class InventoryWasteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryWaste::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
         return $this->createQueryBuilder('w')
             ->orderBy('w.datum', 'DESC');
    }

}