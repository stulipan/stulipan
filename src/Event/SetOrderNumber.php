<?php

declare(strict_types=1);

namespace App\Event;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Order;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * This has to be configured in services.yaml
 */
class SetOrderNumber
{
    /**
     * Creates Order number and sets Order's Customer
     * @param LifeCycleEventArgs $args
     */
    public function postPersist(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Order) {
            $today = new DateTime('now');
            $orderNumber =  (string) GeneralUtils::ORDER_NUMBER_FIRST_DIGIT
                .(GeneralUtils::ORDER_NUMBER_RANGE + $entity->getId())
                .$today->format('d');
            $entity->setNumber($orderNumber);

            $args->getEntityManager()->persist($entity);
            $args->getEntityManager()->flush();
        }
    }
}