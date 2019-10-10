<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;
use Doctrine\ORM\EntityRepository;

class OrderRepository extends EntityRepository
{
    public function findOneById($id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $start
     * @param $end
     * @return \Doctrine\ORM\Query
     */
    public function findAllBetweenDates($start, $end)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.created_at >= :start')
            ->andWhere('p.created_at <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
        ;
        return $qb;
    }

    /**
     * @param $start
     * @param $end
     * @return \Doctrine\ORM\Query
     */
    public function sumAllBetweenDates($start, $end)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.created_at >= :start')
            ->andWhere('p.created_at <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.createdAt', 'DESC')
//            ->select('SUM(p.keszpenz) as keszpenz, SUM(p.bankkartya) as bankkartya', 'SUM(p.kassza) as kassza')
            ->getQuery()
        ;
        return $qb;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ;
    }
    
    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllByQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->where('p.status IS NOT NULL')
//            ->setParameter('status', 1)
            ->getQuery()
            ;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findLast($customer, $status)   //nincs hasznalatban
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->where('p.customer = :customer')
            ->andWhere('p.status = :status')
            ->setParameters(['customer' => $customer, 'status' => $status])
            ->getQuery()
            ;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function sumAllQueryBuilder()
    {
        return $this->createQueryBuilder('p')
//            ->select('SUM(p.keszpenz) as keszpenz, SUM(p.bankkartya) as bankkartya', 'SUM(p.kassza) as kassza')
            ->getQuery()
            ;
    }

}