<?php

declare(strict_types=1);

namespace App\Event;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Order;
use App\Entity\OrderLogChannel;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Event\OrderEvent;
use App\Entity\OrderLog;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OrderSubscriber implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    private $translator;
    private $twig;

    public function __construct(SessionInterface $session, EntityManagerInterface $em, TranslatorInterface $translator, Environment $twig)
    {
        $this->session = $session;
        $this->em = $em;
        $this->translator = $translator;
        $this->twig = $twig;

    }

    public static function getSubscribedEvents(): array
    {
        return [
            // az 'onPaymentStatusUpdate' egy method ami lefuttat és amit nekem kell definialni. Lasd lent, ott van definialva
            OrderEvent::ORDER_CREATED => 'onOrderCreate',
            OrderEvent::ORDER_UPDATED => 'onOrderCreate',
            OrderEvent::PAYMENT_UPDATED => 'onPaymentStatusUpdate',
            OrderEvent::DELIVERY_DATE_UPDATED => 'onDeliveryDateUpdate',

        ];
    }

    public function onOrderCreate(OrderEvent $event): void
    {
        /** Put orderId into session */
        $this->session->set('orderId', $event->getSubject()->getId());

        /** On order creation generate and assign automatically an Order Number */
        /** @var Order $order */
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());

        if ($event->hasArgument('orderStatus')) {
            $shortcode = $event->getArgument('orderStatus');
        }
        if (!isset($shortcode)) {
            throw new Exception('STUPID: OrderSubscriber >> onOrderCreate() függvényben >> nincs \'statusShortcode\' definiálva!');
        }

        $messages = [
            'created' => '{{fullname}} placed this order.',
            'fulfilled' => 'The order was successfully fulfilled and closed.',
            'rejected' => 'The order was rejected.',
            'deleted' => 'The order was deleted.',
        ];

        switch ($shortcode) {
            case OrderStatus::STATUS_CREATED:
                $message = $this->translator->trans($messages[$shortcode], [
                    '{{fullname}}' => $order->getFullname(),
                ]);
                break;
            case OrderStatus::STATUS_FULFILLED || OrderStatus::STATUS_REJECTED || OrderStatus::STATUS_DELETED:
                $message = $this->translator->trans($messages[$shortcode], []);
                break;
//            case OrderStatus::STATUS_REJECTED:
//                $message = $this->translator->trans($messages[$shortcode], []);
//                break;
        }

        $log = new OrderLog();
        $log->setMessage($message);
        if (isset($description) && $description) {
            $log->setDescription($description);
        }
        $log->setChannel($this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
        $log->setOrder($order);
        $order->addLog($log);

        $this->em->persist($order);
        $this->em->flush();
    }

//    public function onOrderUpdate(OrderEvent $event): void
//    {
//        /** Put orderId into session */
//        $this->session->set('orderId', $event->getSubject()->getId());
//
//        /** On order creation generate and assign automatically an Order Number */
//        /** @var Order $order */
//        $order = $this->em->getRepository(Order::class)->findOneById($event->getSubject()->getId());
//
//        if ($event->hasArgument('orderStatus')) {
//            $shortcode = $event->getArgument('orderStatus');
//        }
//        if (!isset($shortcode)) {
//            throw new \Exception('STUPID: OrderSubscriber >> onOrderUpdate() függvényben >> nincs \'statusShortcode\' definiálva!');
//        }
//
//        $messages = [
//            'created' => '{{fullname}} placed this order.',
//            'fulfilled' => 'The order was successfully fulfilled and closed.',
//            'rejected' => 'The order was rejected.',
//            'deleted' => 'The order was deleted.',
//        ];
//
//        switch ($shortcode) {
//            case OrderStatus::STATUS_CREATED:
//                $message = $this->translator->trans($messages[$shortcode], [
//                    '{{fullname}}' => $order->getFullname(),
//                ]);
//                break;
//            case OrderStatus::STATUS_FULFILLED || OrderStatus::STATUS_REJECTED || OrderStatus::STATUS_DELETED:
//                $message = $this->translator->trans($messages[$shortcode], []);
//                break;
////            case OrderStatus::STATUS_REJECTED:
////                $message = $this->translator->trans($messages[$shortcode], []);
////                break;
//        }
//
//        $log = new OrderLog();
//        $log->setMessage($message);
//        if (isset($description) && $description) {
//            $log->setDescription($description);
//        }
//        $log->setChannel($this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
//        $log->setOrder($order);
//        $order->addLog($log);
//
//        $this->em->persist($order);
//        $this->em->flush();
//    }

    public function onDeliveryDateUpdate(OrderEvent $event): void
    {
        /** @var Order $order */
//        $order = $this->em->getRepository(Order::class)->findOneById($event->getSubject()->getId());
        $order = $event->getSubject();

//        $shortcode = $event->getArgument('orderStatus');
//        if (!isset($shortcode)) {
//            throw new \Exception('STUPID: OrderSubscriber >> onPaymentStatusUpdate fügvényben >> nincs \'statusShortcode\' definiálva!');
//        }

        if (!$event->getArgument('channel')) {
            throw new Exception('STUPID: OrderSubscriber >> onDeliveryDateUpdate() függvényben >> nincs \'channel\' definiálva!');
        }
        $channel = $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]);

        $message = 'Szállítási idő rögzítve: {{date}}, {{interval}} óra között.';
        $message = $this->translator->trans($message, [
            '{{date}}' => $order->getDeliveryDate()->format('Y-m-d'),
            '{{interval}}' => $order->getDeliveryInterval(),
        ]);

        $log = new OrderLog();
        $log->setMessage($message);
        if (isset($description) && $description) {
            $log->setDescription($description);
        }
        $log->setChannel($channel);
        $log->setOrder($order);
        $order->addLog($log);
        $this->em->persist($order);
        $this->em->flush();
    }

    public function onPaymentStatusUpdate(OrderEvent $event): void
    {
        /** @var Order $order */
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());

        $shortcode = $event->getArgument('paymentStatus');
        if (!isset($shortcode)) {
            throw new Exception('STUPID: OrderSubscriber >> onPaymentStatusUpdate() függvényben >> nincs \'statusShortcode\' definiálva!');
        }

        $messages = [
            'pending' => 'A {{amount}} payment is pending on {{payment}}.',
            'paid' => 'A payment of {{amount}} has been paid by the customer.',
            'partially_paid' => 'A partial payment of {{amount}} has been paid by the customer.',
            'partially_refunded' => 'A partial refund of {{amount}} has been issue to the customer.',
            'refunded' => 'A {{amount}} has been refunded to the customer.',
        ];
        $descriptions = [
            'pending' => $this->twig->render('admin/order/_history-pending.html.twig', [
                'order' => $order,
            ]),
//            'paid' => '',
//            'partially_paid' => '',
//            'partially_refunded' => '',
//            'refunded' => '',
        ];

        $money = $this->twig->getFilter('money')->getCallable();

        $message = null;
        $description = null;
        switch ($shortcode) {
            case PaymentStatus::STATUS_PENDING:
                $message = $this->translator->trans($messages[$shortcode], [
                    '{{amount}}' => $money($order->getSummary()->getTotalAmountToPay()),
                    '{{payment}}' => $order->getPaymentMethod(),
                ]);
                $description = $descriptions[$shortcode];
                break;
            case PaymentStatus::STATUS_PAID:
                $message = $this->translator->trans($messages[$shortcode], [
                    '{{amount}}' => $money($order->getSummary()->getTotalAmountToPay()),
                ]);
                break;
//            case PaymentStatus::STATUS_PARTIALLY_PAID:
//                $message = $this->translator->trans($messages[$shortcode], [
//                    '{{amount}}' => $order->getSummary()->getTotalAmountToPay()
//                ]);
//                break;
//            case PaymentStatus::STATUS_PARTIALLY_REFUNDED:
//                $message = $this->translator->trans($messages[$shortcode], [
//                    '{{amount}}' => $order->getSummary()->getTotalAmountToPay()
//                ]);
//                break;
            case PaymentStatus::STATUS_REFUNDED:
                $message = $this->translator->trans($messages[$shortcode], [
                    '{{amount}}' => $money($order->getSummary()->getTotalAmountToPay())
                ]);
                break;
        }

        $log = new OrderLog();
        $log->setMessage($message);
        if ($description) {
            $log->setDescription($description);
        }
        $log->setChannel($this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
        $log->setOrder($order);
        $order->addLog($log);
        $this->em->persist($order);
        $this->em->flush();
    }
}