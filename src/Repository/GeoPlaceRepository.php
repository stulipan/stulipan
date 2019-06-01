<?php

namespace App\Repository;

use App\Entity\Geo\GeoPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class GeoPlaceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GeoPlace::class);
    }

    public function findAllOrdered()
     {
         $qb = $this->createQueryBuilder('p')
             ->orderBy('p.id', 'ASC')
             ->getQuery();

         return $qb->execute();
     }

    /**
     * @param string $query
     * @param int $limit
     *
     * @return GeoPlace
     */
    public function findAllMatching(string $query, int $limit = 5)
     {
         return $this->createQueryBuilder('p')
             ->select('p.city')
             ->andWhere('p.city LIKE :query')
             ->setParameter('query', '%'.$query.'%')
             ->distinct(true)
             ->orderBy('p.city', 'ASC')
             ->setMaxResults($limit)
             ->getQuery()
             ->getResult();
     }
    
    public function findAllProvinces()
    {
        return $this->createQueryBuilder('p')
            ->select('p.province')
            ->distinct(true)
            ->orderBy('p.province', 'ASC')
            ->getQuery()
            ->getResult();
    }

}