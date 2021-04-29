<?php

namespace App\Repository;

use App\Entity\CmsNavigation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class CmsNavigationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmsNavigation::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllOrdered()
    {
         $qb = $this->createQueryBuilder('p')
             ->andWhere('p.enabled = :enabled')
             ->setParameter('enabled', 1)
//             ->orderBy('p.ordering', 'ASC')
             ;
        return $qb->getQuery();
    }

}