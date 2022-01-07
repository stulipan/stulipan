<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DateRange;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\PaymentTransaction;
use App\Services\HelperFunction;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class OrderRepository extends ServiceEntityRepository
{
    private $settings;
    private $localization;
    private $helper;

    public function __construct(ManagerRegistry $registry, StoreSettings $settings,
                                Localization $localization, HelperFunction $helper)
    {
        parent::__construct($registry, Order::class);
        $this->settings = $settings;
        $this->localization = $localization;
        $this->helper = $helper;
    }

    /**
     * Return all Orders in the last X period of time. Only REAL orders --> status shortcode == 'created'
     * @return array
     * @return Query
     * @throws Exception
     */
    public function findAllLast($period)
    {
        $date = new DateTime();
        if ($period != '24 hours' && $period != '7 days' && $period != '30 days') {
            return null;
        }

        if ($period == '24 hours') {
            $date->modify('-24 hours')->setTime(0,0);
        }
        if ($period == '7 days') {
            $date->modify('-6 days')->setTime(0,0);
        }
        if ($period == '30 days') {
            $date->modify('-29 days')->setTime(0,0);
        }

        $status = OrderStatus::ORDER_CREATED;
        $status = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $status]);

        $qb = $this
            ->createQueryBuilder('o')
            ->where('o.postedAt >= :date')
            ->andWhere('o.status = :status')
            ->setParameter('date', $date)
            ->setParameter('status', $status)
            ->orderBy('o.postedAt', 'DESC')
            ->getQuery()
            ->getResult()
            //->execute() // vagy ezzel is mukodik
        ;
        return $qb;
    }

    /**
     * Counts all Orders in the last X period of time. Only REAL orders --> status IS NOT NULL
     * @param string $period            Eg: '24 hours', '7 days'  Value must be a date/time string
     * @param array $filter             It is used to filter orders.
     *                                  If no filter is set, it will count all orders.
     *             [
     *                'paymentStatus' => 'pending'
     *                'orderStatus' => 'created'
     *             ]
     * @return float
     * @throws Exception
     */
    public function countLast($filter = [])
    {
        if (!is_array($filter)) {
            return 0;
        }
        if (array_key_exists('period', $filter)) {
            $period = $filter['period'];
        }
        if (array_key_exists('dateRange', $filter)) {
            $dateRange = $filter['dateRange'];
        }

        if ($period && $period != '24 hours' && $period != '7 days' && $period != '30 days' && $period !== 'lifetime' && $period !== 'dateRange') {
            return 0;
        }

        if ($period === 'dateRange' && !$dateRange) {
            return 0;
        }

        $qb = $this
            ->createQueryBuilder('o');
        $qb
            ->select('COUNT(o.id) as count')   // COUNT
            ->andWhere('o.postedAt IS NOT NULL')  // azaz letrejott
            ->andWhere('o.status IN (:statusList)')
            ->andWhere('o.canceledAt IS NULL')
//            ->andWhere( $qb->expr()->in('o.status', ':statusList')) // equivalent a fentivel
        ;

        if ($period === null || $period === 'lifetime') {

        } else {
            $date = new DateTime();
            $dateRange = new DateRange();

            if ($period == '24 hours') {
//                $date->modify('-24 hours')->setTime(0,0);
                $dateRange->setStart($date->modify('-24 hours'));
                $dateRange->setEnd($dateRange->getStart());
            }
            if ($period == '7 days') {
//                $date->modify('-6 days')->setTime(0,0);
                $dateRange->setStart($date->modify('-6 days'));
                $dateRange->setEnd($dateRange->getStart());
            }
            if ($period == '30 days') {
//                $date->modify('-29 days')->setTime(0,0);
                $dateRange->setStart($date->modify('-29 days'));
                $dateRange->setEnd($dateRange->getStart());
            }

            if ($period == 'dateRange') {
                $splitPieces = explode(" - ", $filter['dateRange']);
                $start = $splitPieces[0];
                $end = $splitPieces[1];
                $format = $this->localization->getCurrentLocale()->getDateFormat();

                if (!isset($start) or $start === null or $start == "") {
                } else {
                    $dateRange->setStart(DateTime::createFromFormat($format, $start));
//                    $start = $dateRange->getStart();
                }
                if (!isset($end) or $end === null or $end == "") {
                } else {
                    $dateRange->setEnd(DateTime::createFromFormat($format, $end));
//                    $end = $dateRange->getEnd();
                }
            }

            $qb->andWhere('o.postedAt >= :start')
                ->setParameter('start', $dateRange->getStart())
            ;

            if ($dateRange->getStart() <= $dateRange->getEnd()) {
                $qb->andWhere('o.postedAt <= :end')
                    ->setParameter('end', $dateRange->getEnd())
                ;
            }

        }

        $statusList = [
            $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::ORDER_CREATED]),
            $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::STATUS_FULFILLED]),
        ];

        if (array_key_exists('orderStatus', $filter)) {
            $orderStatus = $filter['orderStatus'];
            $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

            $statusList = [$orderStatus];
        }
        if (count($statusList) >0) {
            $qb->setParameter('statusList', $statusList);
        }

        if (array_key_exists('paymentStatus', $filter)) {
            $paymentStatus = $filter['paymentStatus'];
            $paymentStatus = $this->getEntityManager()->getRepository(PaymentTransaction::class)->findOneBy(['status' => $paymentStatus]);

            $qb->leftJoin('o.transactions', 't');
            $qb->andWhere('t.status = :tStatus')
                ->setParameter('tStatus', $paymentStatus)
            ;
//            $qb->andWhere('o.paymentStatus = :status')
//                ->setParameter('status', $paymentStatus)
//            ;
        }
        if (array_key_exists('isCanceled', $filter) && $filter['isCanceled'] == false) {
            $qb->andWhere('o.canceledAt IS NULL')
            ;
        }

        $query = $qb->getQuery()->getSingleScalarResult();
        return $query == null ? 0 : $query;
    }

    /**
     *      ->sumLast('30 days', [
     *                      'period' => '30 days',
     *                      'isCanceled' => true,
     *                      'orderStatus' => OrderStatus::STATUS_FULFILLED,
     *                      'paymentStatus' => OrderStatus::STATUS_PENDING,
     *                 ])
     */
    public function sumLast($filter = [])
    {
        if (!is_array($filter)) {
            return 0;
        }
        if (array_key_exists('period', $filter)) {
            $period = $filter['period'];
        }
        if (array_key_exists('dateRange', $filter)) {
            $dateRange = $filter['dateRange'];
        }
        if ($period && $period != '24 hours' && $period != '7 days' && $period != '30 days' && $period !== 'lifetime' && $period !== 'dateRange') {
            return 0;
        }
        if ($period === 'dateRange' && !$dateRange) {
            return 0;
        }
        $params = [];

        if ($period === null || $period === 'lifetime') {
            $params['startDate'] = (new DateTime('-100 years'))->format('Y-m-j');
        } else {
            $date = new DateTime();
            $dateRange = new DateRange();

            if ($period == '24 hours') {
                $dateRange->setStart($date->modify('-24 hours'));
                $dateRange->setEnd($dateRange->getStart());
            }
            if ($period == '7 days') {
                $dateRange->setStart($date->modify('-6 days'));
                $dateRange->setEnd($dateRange->getStart());
            }
            if ($period == '30 days') {
                $dateRange->setStart($date->modify('-29 days'));
                $dateRange->setEnd($dateRange->getStart());
            }
            if ($period == 'dateRange') {
                $splitPieces = explode(" - ", $filter['dateRange']);
                $start = $splitPieces[0];
                $end = $splitPieces[1];
                $format = $this->localization->getCurrentLocale()->getDateFormat();

                if (!isset($start) or $start === null or $start == "") {
                } else {
                    $dateRange->setStart(DateTime::createFromFormat($format, $start));
                }
                if (!isset($end) or $end === null or $end == "") {
                } else {
                    $dateRange->setEnd(DateTime::createFromFormat($format, $end));
                }
            }
            $params['startDate'] = $dateRange->getStart()->format('Y-m-j H:i:s');

            if ($dateRange->getStart() <= $dateRange->getEnd()) {
                $sqlEndDate = "
                    AND o.posted_at <= :endDate
                ";
                $params['endDate'] = $dateRange->getEnd()->format('Y-m-j H:i:s');
            }
        }

//        SELECT sum(s.summa)
//        FROM (
//            SELECT sum(i.price_total), o.shipping_fee, o.payment_fee, sum(i.price_total) + o.shipping_fee + o.payment_fee as summa
//                FROM cart_order_2 as o
//                RIGHT JOIN cart_order_item as i ON o.id=i.order_id
//                WHERE o.status_id = 1 AND o.posted_at > '2021-11-04'
//                GROUP BY i.order_id
//            ) as s

        if (array_key_exists('isCanceled', $filter) && $filter['isCanceled'] == true) {
            $sqlCanceledAt = "
                AND o.canceled_at IS NOT NULL
            ";
        }

        $statusList = [];
        $statusList[] = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::ORDER_CREATED])->getId();
        $statusList[] = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::STATUS_FULFILLED])->getId();
        $statusList = '('.implode(',', $statusList).')';

        if (array_key_exists('orderStatus', $filter)) {
            $orderStatus = $filter['orderStatus'];
            $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus])->getId();

            $statusList = [];
            $statusList[$filter['orderStatus']] = $orderStatus;
            $statusList = '('.implode(',', $statusList).')';
        }
        if ($statusList != '') {
            $sqlStatusList = "AND o.status_id IN ". $statusList ." 
            ";
        }

        $sql = " 
            SELECT
                sum(s.summa) AS summa
            FROM
                (SELECT
                   sum(IFNULL(i.price_total,0)),
                   IFNULL(o.shipping_fee,0), 
                   IFNULL(o.payment_fee,0), 
                   sum(IFNULL(i.price_total,0)) + IFNULL(o.shipping_fee,0) + IFNULL(o.payment_fee,0) AS summa
                FROM cart_order_2 o
                RIGHT JOIN cart_order_item i ON o.id=i.order_id
                WHERE 
                      o.posted_at IS NOT NULL 
                  AND o.posted_at >= :startDate
                  AND o.canceled_at IS NULL 
        ";

        if (isset($sqlEndDate)) {
            $sql .= $sqlEndDate;
        }

        if (isset($sqlStatusList)) {
            $sql .= $sqlStatusList;
        }

        if (isset($sqlCanceledAt)) {
            $sql .= $sqlCanceledAt;
        }

        $sql .= "GROUP BY i.order_id
                    ) s
        ";
        $conn = $this->getEntityManager()->getConnection();
        $statement = $conn->prepare($sql);

        $statement->execute($params);
        $query = $statement->fetchOne();
        return $query == null ? 0 : (float) $query;
    }

    /**
     * Used with PagerFanta in Admin\OrderController.
     *
     * Fetches orders
     *  - using some filtering criteria
     *  - in the last X period of time. Only REAL orders --> status IS NOT NULL
     * @param array $filters             It is used to filter orders.
     *                                  If no filter is set, it will count all orders.
     *             [
     *                  'dateRange' => '2018-07-25 - 2019-11-23'
     *                  'searchTerm => 'valami'
     *                  'paymentStatus' => 'pending'
     *                  'orderStatus' => 'created'
     *             ]
     * @return Query
     * @throws Exception
     */
    public function findAllQuery($filters = [], $onlyPlacedOrders = true)
    {
        $qb = $this->createQueryBuilder('o');

        if ($onlyPlacedOrders) {
            $qb->andWhere('o.postedAt IS NOT NULL');  // azaz letrejott
        }

        $qb->orderBy('o.postedAt', 'DESC');

        if (is_array($filters)) {
            if (array_key_exists('searchTerm', $filters) && $filters['searchTerm'] !== null) {
                $searchTerm = strtolower($filters['searchTerm']);
//                $qb->andWhere('o.id LIKE :id OR
//                                o.number LIKE :number OR
//                                LOWER(o.email) LIKE :email OR
//                                LOWER(o.firstname) LIKE :firstname OR
//                                LOWER(o.lastname) LIKE :lastname OR
//                                LOWER(CONCAT(o.firstname,\' \',o.lastname)) LIKE :fullname1 OR
//                                LOWER(CONCAT(o.lastname,\' \',o.firstname)) LIKE :fullname2 OR
//                                o.billingPhone LIKE :billingPhone OR
//                                o.shippingPhone LIKE :shippingPhone OR
//                                LOWER(o.shippingName) LIKE :shippingName OR
//                                LOWER(o.billingName) LIKE :billingName
//                                ')
//                    ->setParameter('id', '%'.$searchTerm.'%')
//                    ->setParameter('number', '%'.$searchTerm.'%')
//                    ->setParameter('email', '%'.$searchTerm.'%')
//                    ->setParameter('firstname', '%'.$searchTerm.'%')
//                    ->setParameter('lastname', '%'.$searchTerm.'%')
//                    ->setParameter('fullname1', '%'.$searchTerm.'%')
//                    ->setParameter('fullname2', '%'.$searchTerm.'%')
//                    ->setParameter('billingPhone', '%'.$searchTerm.'%')
//                    ->setParameter('shippingPhone', '%'.$searchTerm.'%')
//                    ->setParameter('shippingName', '%'.$searchTerm.'%')
//                    ->setParameter('billingName', '%'.$searchTerm.'%')
//                ;

                $comparisonsX = [
                    $qb->expr()->like('o.id',':id'),
                    $qb->expr()->like('o.number', ':number'),
                    $qb->expr()->like('LOWER(o.email)', ':email'),
                    $qb->expr()->like('LOWER(o.firstname)',':firstname'),
                    $qb->expr()->like('LOWER(o.lastname)', ':lastname'),
                    $qb->expr()->like('LOWER(CONCAT(o.firstname,\' \',o.lastname))',':fullname1'),
                    $qb->expr()->like('LOWER(CONCAT(o.lastname,\' \',o.firstname))',':fullname2'),
                    $qb->expr()->like('o.billingPhone', ':billingPhone'),
                    $qb->expr()->like('o.shippingPhone', ':shippingPhone'),
                    $qb->expr()->like('LOWER(CONCAT(o.shippingFirstname,\' \',o.shippingLastname))',':shippingName1'),
                    $qb->expr()->like('LOWER(CONCAT(o.shippingLastname,\' \',o.shippingFirstname))',':shippingName2'),
                    $qb->expr()->like('LOWER(CONCAT(o.billingFirstname,\' \',o.billingLastname))',':billingName1'),
                    $qb->expr()->like('LOWER(CONCAT(o.billingLastname,\' \',o.billingFirstname))',':billingName2'),
                ];
                $paramsX = [
                    'id' => '%'.$searchTerm.'%',
                    'number' => '%'.$searchTerm.'%',
                    'email' => '%'.$searchTerm.'%',
                    'firstname' => '%'.$searchTerm.'%',
                    'lastname' => '%'.$searchTerm.'%',
                    'fullname1' => '%'.$searchTerm.'%',
                    'fullname2' => '%'.$searchTerm.'%',
                    'billingPhone' => '%'.$searchTerm.'%',
                    'shippingPhone' => '%'.$searchTerm.'%',
                    'shippingName1' => '%'.$searchTerm.'%',
                    'shippingName2' => '%'.$searchTerm.'%',
                    'billingName1' => '%'.$searchTerm.'%',
                    'billingName2' => '%'.$searchTerm.'%',
                ];

                // If $searchTerms contains several words (Eg: renata jr fazekas)
                // then we will search id db for every permutation of it.
                $words = explode( ' ', $searchTerm);
                $searchTermPermutations = $this->helper->pc_permute($words);

                foreach ($searchTermPermutations as $key => $item) {
                    // The following line executes $qb->expr()->orX() with arguments from the array $comparisonsX
                    $orX = call_user_func_array([$qb->expr(), 'orX'], $comparisonsX);

//                    $paramsX['shippingName_'.$key] = '%'.implode(' ', $item).'%';
//                    $paramsX['billingName_'.$key] = '%'.implode(' ', $item).'%';
                }
                $qb->andWhere($orX)->setParameters($paramsX);
            }

            if (array_key_exists('dateRange', $filters) && $filters['dateRange'] !== null) {
                $splitPieces = explode(" - ", $filters['dateRange']);
                $start = $splitPieces[0];
                $end = $splitPieces[1];
                $format = $this->localization->getCurrentLocale()->getDateFormat();

                $dateRange = new DateRange();
                if (!isset($start) or $start === null or $start == "") {
                } else {
//                    $dateRange->setStart(DateTime::createFromFormat('!Y-m-d',$start));
                    $dateRange->setStart(DateTime::createFromFormat($format, $start));
                    $start = $dateRange->getStart();
                }
                if (!isset($end) or $end === null or $end == "") {
                } else {
//                    $dateRange->setEnd(DateTime::createFromFormat('!Y-m-d',$end));
                    $dateRange->setEnd(DateTime::createFromFormat($format, $end));
                    $end = $dateRange->getEnd();
                }

                $qb->andWhere('o.postedAt >= :start')
                    ->andWhere('o.postedAt <= :end')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                ;
            }

            if (array_key_exists('paymentStatus', $filters) && $filters['paymentStatus'] !== null) {
                $paymentStatus = $filters['paymentStatus'];
//                $paymentStatus = $this->getEntityManager()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);
//
//                $qb->andWhere('o.paymentStatus = :paymentStatus')
//                    ->setParameter('paymentStatus', $paymentStatus)
//                ;
                $paymentStatus = $this->getEntityManager()->getRepository(PaymentTransaction::class)->findOneBy(['status' => $paymentStatus]);

                $qb->leftJoin('o.transactions', 't');

//                ORDER BY id DESC LIMIT 1;
                $qb->andWhere('t.status = :tStatus')
                    ->setParameter('tStatus', $paymentStatus)
                ;
            }
            if (array_key_exists('orderStatus', $filters) && $filters['orderStatus'] !== null) {
                $orderStatus = $filters['orderStatus'];
                $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

                $qb->andWhere('o.status = :orderStatus')
                    ->setParameter('orderStatus', $orderStatus)
                ;
            }
            if (array_key_exists('isCanceled', $filters) && $filters['isCanceled'] !== null) {
                if ( $filters['isCanceled'] === 'yes') {
                    $qb->andWhere('o.canceledAt IS NOT NULL');
                } else {
                    $qb->andWhere('o.canceledAt IS NULL');
                }
            }
        }
        $query = $qb->getQuery();
        return $query;
    }

    /**
     * @return Query
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
     * @param array $criteria
     *
     * @return Query
     */
    public function findLastPartialOrder(array $criteria)
    {
        $qb = $this
            ->createQueryBuilder('o')
            ->andWhere('o.status IS NULL')
        ;
        $qb->orderBy('o.id', 'DESC');

        if (is_array($criteria)) {
            if (array_key_exists('customer', $criteria) && $criteria['customer']) {
                $customer = $criteria['customer'];

                $qb->andWhere('o.customer = :customer')
                    ->setParameter('customer', $customer);

                $resultArray = $qb->getQuery()->getResult();
                if (count($resultArray) > 0) {
                    return $qb->getQuery()->getResult()[0];
                }
                return null;
            }
        }

        return null;
    }
}