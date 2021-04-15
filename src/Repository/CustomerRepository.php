<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Geo\GeoPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 *      !!! NINCS HASZNALVA !!!!!!!!!!!!!!!!
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @param array $criteria
     *
     * @return Query
     */
    public function findLastPartialOrder(array $criteria)
     {
         $qb = $this->createQueryBuilder('c');
         $qb->orderBy('c.id', 'ASC');

         if (is_array($criteria)) {
             if (array_key_exists('email', $criteria) && $criteria['email']) {
                 $qb->andWhere('c.email LIKE :email');
                 $qb->setParameter('email', '%'.$criteria['email'].'%');

                 return $qb->getQuery();
             }
         }

         return null;
     }
}