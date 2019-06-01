<?php

namespace App\Repository;

use App\Entity\Inventory\InventorySupply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class InventorySupplyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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