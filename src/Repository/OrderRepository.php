<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\DateRange;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Services\HelperFunction;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class OrderRepository extends ServiceEntityRepository  // ServiceEntityRepository instead of classical EntityRepository
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
            $date->modify('-24 hours');
        }
        if ($period == '7 days') {
            $date->modify('-7 days');
        }
        if ($period == '30 days') {
            $date->modify('-30 days');
        }

        $status = OrderStatus::ORDER_CREATED;
        $status = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $status]);

        $qb = $this
            ->createQueryBuilder('o')
            ->where('o.createdAt > :date')
            ->andWhere('o.status = :status')
            ->setParameter('date', $date)
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
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
    public function countLast($period = null, $filter = [])
    {
        if ($period && $period != '24 hours' && $period != '7 days' && $period != '30 days' && $period !== 'lifetime') {
            return 0;
        }

        $qb = $this
            ->createQueryBuilder('o')
            ->select('COUNT(o.id) as count')   // COUNT
            ->andWhere('o.status IS NOT NULL')
            ->orderBy('o.createdAt', 'DESC')
        ;

        if ($period === null || $period === 'lifetime') {

        } else {
            $date = new DateTime();
            if ($period == '24 hours') {
                $date->modify('-24 hours');
            }
            if ($period == '7 days') {
                $date->modify('-7 days');
            }
            if ($period == '30 days') {
                $date->modify('-30 days');
            }
            $qb->andWhere('o.createdAt > :date')
                ->setParameter('date', $date)
            ;
        }

        if (is_array($filter)) {
            if (array_key_exists('paymentStatus', $filter)) {
                $paymentStatus = $filter['paymentStatus'];
                $paymentStatus = $this->getEntityManager()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);

                $qb->andWhere('o.paymentStatus = :status')
                    ->setParameter('status', $paymentStatus)
                ;
            }
            if (array_key_exists('orderStatus', $filter)) {
                $orderStatus = $filter['orderStatus'];
                $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

                $qb->andWhere('o.status = :status')
                    ->setParameter('status', $orderStatus)
                ;
            }
        }
        $query = $qb->getQuery()->getSingleScalarResult();
        return $query == null ? 0 : $query;
    }

    public function sumLast($period = null, $filter = [])
    {
        if ($period && $period != '24 hours' && $period != '7 days' && $period != '30 days' && $period !== 'lifetime') {
            return 0;
        }

        $qb = $this
            ->createQueryBuilder('o')
            ->andWhere('o.status IS NOT NULL')
            ->leftJoin('o.items', 'i')
//            ->select('SUM(i.priceTotal) as totalRevenue')
            ->select('(SUM(i.priceTotal) + SUM(o.shippingPrice)) as totalRevenue')
        ;

        if ($period === null || $period === 'lifetime') {

        } else {
            $date = new DateTime();
            if ($period == '24 hours') {
                $date->modify('-24 hours');
            }
            if ($period == '7 days') {
                $date->modify('-7 days');
            }
            if ($period == '30 days') {
                $date->modify('-30 days');
            }
            $qb->andWhere('o.createdAt > :date')
                ->setParameter('date', $date)
            ;
        }

        if (is_array($filter)) {
            if (array_key_exists('paymentStatus', $filter)) {
                $paymentStatus = $filter['paymentStatus'];
                $paymentStatus = $this->getEntityManager()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);

                $qb->andWhere('o.paymentStatus = :status')
                    ->setParameter('status', $paymentStatus)
                ;
            }
            if (array_key_exists('orderStatus', $filter)) {
                $orderStatus = $filter['orderStatus'];
                $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

                $qb->andWhere('o.status = :status')
                    ->setParameter('status', $orderStatus)
                ;
            }
        }
        $query = $qb->getQuery()->getSingleScalarResult();
        return $query == null ? 0 : (float) $query;
    }

    /**
     * Used with PagerFanta in OrderController.
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
//    public function findAllQuery($filters = [])
    public function findAllQuery($filters = [], $onlyPlacedOrders = true)
    {
        $qb = $this->createQueryBuilder('o');

        if ($onlyPlacedOrders) {
            $qb->andWhere('o.status IS NOT NULL');
        }

        $qb->orderBy('o.createdAt', 'DESC');

        if (is_array($filters)) {
            if (array_key_exists('searchTerm', $filters) && $filters['searchTerm']) {
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

            if (array_key_exists('dateRange', $filters) && $filters['dateRange']) {
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

                $end->modify('24 hours'); // Ez nelkül az $end mindig az adott nap 00:00:00 óráját veszi, ergó az aznapi rendelések kimaradnak
                $qb->andWhere('o.createdAt >= :start')
                    ->andWhere('o.createdAt <= :end')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                ;
            }

            if (array_key_exists('paymentStatus', $filters) && $filters['paymentStatus']) {
                $paymentStatus = $filters['paymentStatus'];
                $paymentStatus = $this->getEntityManager()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);

                $qb->andWhere('o.paymentStatus = :paymentStatus')
                    ->setParameter('paymentStatus', $paymentStatus)
                ;
            }
            if (array_key_exists('orderStatus', $filters) && $filters['orderStatus']) {
                $orderStatus = $filters['orderStatus'];
                $orderStatus = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);

                $qb->andWhere('o.status = :orderStatus')
                    ->setParameter('orderStatus', $orderStatus)
                ;
            }
        }
        $query = $qb->getQuery();
        return $query;
    }

    /**
     * Return all Orders in the last X period of time. Only REAL orders --> status shortcode == 'created'
     * @return array
     * @return Query
     *@throws Exception
     */
    public function summaryAllLast($period)
    {
        $date = new DateTime();
        if ($period != '24 hours' && $period != '7 days' && $period != '30 days') {
            return null;
        }

        if ($period == '24 hours') {
            $date->modify('-24 hours');
        }
        if ($period == '7 days') {
            $date->modify('-7 days');
        }
        if ($period == '30 days') {
            $date->modify('-30 days');
        }

        $status = OrderStatus::ORDER_CREATED;
        $status = $this->getEntityManager()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $status]);

        $qb = $this
            ->createQueryBuilder('o')
//            ->select('COUNT(o.id) as count')
//            ->select('o.id, SUM(oi.priceTotal)+o.deliveryFee as totalAmountToPay')
            ->select('o.id, SUM(oi.priceTotal)+o.deliveryFee as totalAmountToPay, COUNT(oi.id) as itemCount')
//            ->from(OrderItem::class, 'oi')
            ->join(OrderItem::class,'oi','WITH', 'o=oi.order')
            ->groupBy('o.id')
            ->orderBy('o.id')

            ->where('o.createdAt > :date')
            ->andWhere('o.status = :status')
            ->setParameter('date', $date)
            ->setParameter('status', $status)
            ->getDQL()
//            ->getQuery()
//            ->getResult()
        ;
//        dd($qb);

        $date = $date->format('Y-m-d H:i:s');
//        dd($date);
        $sql = "SELECT SUM(t.totalAmountToPay) AS totalAmountToPay
                FROM cart_order_2 o, (
                    SELECT o.id, COUNT(oi.product_id) AS itemCount, SUM(oi.price_total)+o.delivery_fee AS totalAmountToPay
                    FROM cart_order_2 o, cart_order_item oi
                    WHERE o.id = oi.order_id AND o.created_at > '.$date.' AND o.status_id = 1
                    GROUP BY o.id
                    ORDER BY o.id
                    ) t"
            ;
//        AND o.status = :status
        $result = $this->_em->getConnection()->prepare($sql);
        $result->execute();
        return $result->fetchAll();
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