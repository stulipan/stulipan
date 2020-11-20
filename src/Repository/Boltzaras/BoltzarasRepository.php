<?php
// src/Repository/BoltzarasRepository.php
// az adatbázisból kiolvasás funkciókat a repository-ban tároljuk

namespace App\Repository\Boltzaras;

use App\Entity\Boltzaras\Boltzaras;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class BoltzarasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Boltzaras::class);
    }

    /**
     * @param $kassza
	 * @return Query
     */
    public function findAllGreaterThanKassza($kassza)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('p')
            ->where('p.kassza > :k')
//            ->andWhere('p.kassza > :k')
            ->setParameter('k', $kassza)
            //->orderBy('p.kassza', 'ASC')
            ->getQuery();

        return $qb;
	}

    /**
     * @param $start
     * @param $end
     * @return Query
     */
	public function findAllBetweenDates($start, $end)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.idopont >= :start')
            ->andWhere('p.idopont <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.idopont', 'DESC')
            ->getQuery()
        ;
        return $qb;
    }

    /**
     * @param $start
     * @param $end
     * @return Query
     */
    public function sumAllBetweenDates($start, $end)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.idopont >= :start')
            ->andWhere('p.idopont <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.idopont', 'DESC')
            ->select('SUM(p.keszpenz) as keszpenz, SUM(p.bankkartya) as bankkartya', 'SUM(p.kassza) as kassza')
            ->getQuery()
        ;
        return $qb;
    }
	
    /**
     * @return Query
     */	
    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.idopont', 'DESC')
            ->getQuery()
        ;
	}

    /**
     * @return Query
     */
    public function sumAllQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.keszpenz) as keszpenz, SUM(p.bankkartya) as bankkartya', 'SUM(p.kassza) as kassza')
            ->getQuery()
            ;
    }


}