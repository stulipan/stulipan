<?php

namespace App\Repository;

use App\Entity\Keszlet;
//use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;


class KeszletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Keszlet::class);
    }


    /**
     * @param $id
     * @return Query
     */	
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('boltzaras');
//		return $this->createQueryBuilder('b')
//            ->addSelect('b.*')
//            ->setParameter('user', $userId)
//            ->getQuery();
	}


}