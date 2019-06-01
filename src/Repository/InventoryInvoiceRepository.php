<?php

namespace App\Repository;

use App\Entity\Inventory\InventoryInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class InventoryInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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