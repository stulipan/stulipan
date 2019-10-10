<?php

declare(strict_types=1);

namespace App\Event;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * This has to be configured in services.yaml
 */
class SetOrderNumber
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * @param LifeCycleEventArgs $args
     */
    public function postPersist(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof Order) {
            $today = new \DateTime('now');
            $orderNumber =  (string) GeneralUtils::ORDER_NUMBER_FIRST_DIGIT
                .(GeneralUtils::ORDER_NUMBER_RANGE + $entity->getId())
                .$today->format('d');
            $entity->setNumber($orderNumber);
            $this->em->persist($entity);
            $this->em->flush();
        }
    }
}