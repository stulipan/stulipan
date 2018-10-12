<?php

namespace App\Repository;

use App\Entity\Szamla;
//use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class SzamlaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Szamla::class);
    }


    /**
     * @param $id
     * @return \Doctrine\ORM\Query
     */	
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('Szamla');
//		return $this->createQueryBuilder('b')
//            ->addSelect('b.*')
//            ->setParameter('user', $userId)
//            ->getQuery();
	}


}