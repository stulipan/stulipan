<?php

declare(strict_types=1);

namespace App\Event;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Events;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
            Events::ORDER_CREATED => 'onOrderCreated',
        ];
    }

    public function onOrderCreated(GenericEvent $event): void
    {
        $this->session->set('orderId', $event->getSubject()->getId());

        $order = $this->entityManager->getRepository(Order::class)->findOneById($event->getSubject()->getId());

        $today = new \DateTime('now');
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