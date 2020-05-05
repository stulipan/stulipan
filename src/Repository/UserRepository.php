<?php
// src/Repository/UserRepository.php
namespace App\Repository;

use App\Entity\DateRange;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
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
     *                  'status' => 1  // 1 = enabled, see db.
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
//                $qb->andWhere('
//                                LOWER(u.firstname) LIKE :firstname OR
//                                LOWER(u.lastname) LIKE :lastname OR
//                                LOWER(u.email) LIKE :email
//                                ')
//                    ->setParameter('firstname', '%'.$searchTerm.'%')
//                    ->setParameter('lastname', '%'.$searchTerm.'%')
//                    ->setParameter('email', '%'.$searchTerm.'%')
//                ;
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
                $searchTermPermutations = $this->pc_permute($words);

                foreach ($searchTermPermutations as $key => $item) {
                    array_push($comparisonsX,
                        $qb->expr()->like('LOWER(CONCAT(u.firstname,\' \',u.lastname))', ':fullname_'.$key)
                    );
                    // Execute $qb->expr()->orX() with arguments from the array $comparisonsX
                    $orX = call_user_func_array([$qb->expr(), 'orX'], $comparisonsX);

                    $paramsX['fullname_'.$key] = '%'.implode(' ', $item).'%';
                }
                $qb->andWhere($orX)->setParameters($paramsX);
            }

            if (array_key_exists('status', $filters) && $filters['status']) {
                $status = $filters['status'];
//                $status = $this->getEntityManager()->getRepository(ProductStatus::class)->find($status);

                $qb->andWhere('u.isActive = :status')
                    ->setParameter('status', $status)
                ;
            }
        }
        $query = $qb->getQuery();
        return $query;
    }

    /**
     * Helper function for permutations. Returns an array with all permutations
     *
     * @param $items
     * @param array $perms
     * @return array            # Returns an array with all permutations
     */
    function pc_permute($items, $perms = [])
    {
        if (empty($items)) {
            $return = array($perms);
        } else {
            $return = array();
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $return = array_merge($return, $this->pc_permute($newitems, $newperms));
            }
        }
        return $return;
    }
}