<?php

namespace App\Repository;

use App\Entity\Keszlet;
//use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class KeszletRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Keszlet::class);
    }


    /**
     * @param $id
     * @return \Doctrine\ORM\Query
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