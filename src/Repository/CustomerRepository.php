<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\DateRange;
use App\Services\HelperFunction;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class CustomerRepository extends ServiceEntityRepository
{
    private $helper;

    public function __construct(ManagerRegistry $registry, HelperFunction $helper)
    {
        parent::__construct($registry, Customer::class);
        $this->helper = $helper;
    }

    /**
     * Used with PagerFanta in CustomerController.
     *
     * Fetches customers(users)
     *  - using some filtering criteria
     * @param array $filters             It is used to filter entries.
     *                                  If no filter is set, it will count all entries.
     *             [
     *                  'dateRange' => '2018-07-25 - 2019-11-23'
     *                  'searchTerm => 'valami'
     *                  'acceptsMarketing' => 1  // 1 = enabled, see db.
     *             ]
     * @return Query
     * @throws Exception
     */
    public function findAllQuery($filters = [])
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.isActive IS NOT NULL')
            ->orderBy('u.createdAt', 'DESC')
        ;

        if (is_array($filters)) {
            if (array_key_exists('dateRange', $filters) && $filters['dateRange']) {
                $splitPieces = explode(" - ", $filters['dateRange']);
                $start = $splitPieces[0];
                $end = $splitPieces[1];

                $dateRange = new DateRange();
                if (!isset($start) or $start === null or $start == "") {
                } else {
                    $dateRange->setStart(DateTime::createFromFormat('!Y-m-d',$start));
                    $start = $dateRange->getStart();
                }
                if (!isset($end) or $end === null or $end == "") {
                } else {
                    $dateRange->setEnd(DateTime::createFromFormat('!Y-m-d',$end));
                    $end = $dateRange->getEnd();
                }

                $end->modify('24 hours'); // Ez nelkül az $end mindig az adott nap 00:00:00 óráját veszi, ergó az aznapi rendelések kimaradnak
                $qb->andWhere('o.createdAt >= :start')
                    ->andWhere('o.createdAt <= :end')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                ;
            }
            if (array_key_exists('searchTerm', $filters) && $filters['searchTerm']) {
                $searchTerm = strtolower($filters['searchTerm']);
                $comparisonsX = [
                    $qb->expr()->like('LOWER(u.email)', ':email'),
                    $qb->expr()->like('LOWER(u.firstname)',':firstname'),
                    $qb->expr()->like('LOWER(u.lastname)', ':lastname'),
                ];
                $paramsX = [
                    'email' => '%'.$searchTerm.'%',
                    'firstname' => '%'.$searchTerm.'%',
                    'lastname' => '%'.$searchTerm.'%',
                ];

                // If $searchTerms contains several words (Eg: renata jr fazekas)
                // then we will search id db for every permutation of it.
                $words = explode( ' ', $searchTerm);
                $searchTermPermutations = $this->helper->pc_permute($words);

                foreach ($searchTermPermutations as $key => $item) {
                    array_push($comparisonsX,
                        $qb->expr()->like('LOWER(CONCAT(u.firstname,\' \',u.lastname))', ':fullname_'.$key)
                    );
                    $orX = call_user_func_array([$qb->expr(), 'orX'], $comparisonsX); // Execute $qb->expr()->orX() with arguments from the array $comparisonsX

                    $paramsX['fullname_'.$key] = '%'.implode(' ', $item).'%';
                }
                $qb->andWhere($orX)->setParameters($paramsX);
            }

            if (array_key_exists('acceptsMarketing', $filters) && $filters['acceptsMarketing'] !== null) {
                $status = $filters['acceptsMarketing'];

                $qb->andWhere('u.acceptsMarketing = :acceptsMarketing')
                    ->setParameter('acceptsMarketing', $status)
                ;
            }
        }
        $query = $qb->getQuery();
        return $query;
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