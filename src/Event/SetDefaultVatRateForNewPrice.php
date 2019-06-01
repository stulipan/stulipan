<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Price;
use App\Entity\VatRate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * This has to be configured in services.yaml
 */
class SetDefaultVatRateForNewPrice
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * @param LifeCycleEventArgs $args
     */
    public function prePersist(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Price) {
            $vatRate = $this->em->find(VatRate::class, VatRate::DEFAULT_VAT_RATE);
            $entity->setVatRate($vatRate);
        }
    }
}