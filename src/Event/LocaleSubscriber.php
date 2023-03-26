<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Product\ProductBadge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * !!! NINCS HASZNALATBAN
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;
    private $em;

    public function __construct($defaultLocale, EntityManagerInterface $em)
    {
        $this->defaultLocale = $defaultLocale;
        $this->em = $em;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (true) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter (eg: 'en' in '/en/cart')
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
//            // if no explicit locale has been set on this request, use one from the session
//            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
            $request->setLocale($this->defaultLocale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

}