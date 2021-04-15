<?php

declare(strict_types=1);

namespace App\Event;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Events;
use App\Entity\Order;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * NINCS HASZNALVA !!!!!!!!!!!!!!!!!!!
 */
class OrderGoesToSession implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // az 'onOrderCreated' egy method ami lefuttat és amit nekem kell definialni. Lasd lent, ott van definialva
            Events::ORDER_CREATED => 'onOrderCreatedX',
        ];
    }

    public function onOrderCreatedX(GenericEvent $event): void
    {
        /** Put orderId into session */
        $this->session->set('orderId', $event->getSubject()->getId());

        /** On order creation generate and assign automatically an Order Number */
        /** @var Order $order */
        $order = $this->entityManager->getRepository(Order::class)->find($event->getSubject()->getId());

        $today = new DateTime('now');
//        dd(GeneralUtils::ORDER_NUMBER_FIRST_DIGIT
//            .(GeneralUtils::ORDER_NUMBER_RANGE + $event->getSubject()->getId())
//            .$today->format('d')
//        );
        $orderNumber =  (string) GeneralUtils::ORDER_NUMBER_FIRST_DIGIT
            .(GeneralUtils::ORDER_NUMBER_RANGE + $event->getSubject()->getId())
            .$today->format('d');
        $order->setNumber($orderNumber);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}