<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderGoesToSession implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // az 'onOrderCreated' egy method ami lefuttat és amit nekem kell definialni. Lasd lent, ott van definialva
            Events::ORDER_CREATED => 'onOrderCreated',
        ];
    }

    public function onOrderCreated(GenericEvent $event): void
    {
        $this->session->set('orderId', $event->getSubject()->getId());
    }
}