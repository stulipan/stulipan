<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Host;
use App\Model\Locale;
use App\Services\Localization;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * This has to be configured in services.yaml
 */
class HostSetLocale //implements ServiceSubscriberInterface
{
    /**
     * @var Localization
     */
    private $localization;

    public function __construct(Localization $localization)
    {
        $this->localization = $localization;
    }

    /**
     * @param LifeCycleEventArgs $args
     */
    public function postLoad(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        if ($entity instanceof Host) {
            if ($entity->getLanguageCode() && $entity->getCountryCode()) {
                $entity->setLocale($this->localization->getLocale($entity->getLanguageCode()));
            }
        }
    }
}