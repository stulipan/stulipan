<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Checkout;
use App\Services\HelperFunction;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class CheckoutRepository extends ServiceEntityRepository
{
    private $settings;
    private $localization;
    private $helper;

    public function __construct(ManagerRegistry $registry, StoreSettings $settings,
                                Localization $localization, HelperFunction $helper)
    {
        parent::__construct($registry, Checkout::class);
        $this->settings = $settings;
        $this->localization = $localization;
        $this->helper = $helper;
    }

    /**
     * @param array $criteria
     * @return Query
     */
    public function findLast(array $criteria)
    {
        $qb = $this->createQueryBuilder('o')
        ;
        $qb->orderBy('o.id', 'DESC');

        if (is_array($criteria)) {
            if (array_key_exists('except', $criteria) && $criteria['except']) {
                $exceptedCheckout = $criteria['except'];

                $qb->andWhere('o.id <> :checkoutId')
                    ->setParameter('checkoutId', $exceptedCheckout->getId());
            }
            if (array_key_exists('customer', $criteria) && $criteria['customer']) {
                $customer = $criteria['customer'];

                $qb->andWhere('o.customer = :customer')
                    ->setParameter('customer', $customer);
            }
            $resultArray = $qb->getQuery()->getResult();
            if (count($resultArray) > 0) {
                return $qb->getQuery()->getResult()[0];
            }
        }
        return null;
    }
}