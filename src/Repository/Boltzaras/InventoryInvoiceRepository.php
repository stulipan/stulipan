<?php

namespace App\Repository\Boltzaras;

use App\Entity\Boltzaras\InventoryInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class InventoryInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryInvoice::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */	
    public function findAllOrdered()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.company', 'DESC');
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