<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Order;
use App\Entity\OrderLogChannel;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
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
    private $uuid;

    public function __construct(SessionInterface $session, EntityManagerInterface $em, TranslatorInterface $translator,
                                Environment $twig)
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
            OrderEvent::PRODUCT_ADDED_TO_CART => 'onCartCreate',
//            OrderEvent::ORDER_CREATED => 'onOrderCreate',
            OrderEvent::ORDER_UPDATED => 'onOrderCreate',
            OrderEvent::PAYMENT_UPDATED => 'onPaymentStatusUpdate',
            OrderEvent::DELIVERY_DATE_UPDATED => 'onDeliveryDateUpdate',
            OrderEvent::SET_ORDER_AS_TRACKED => 'onSetOrderAsTracked',
            OrderEvent::EMAIL_SENT_ORDER_CONFIRMATION => 'onEmailSentOrderConfirmation',
            OrderEvent::EMAIL_SENT_SHIPPING_CONFIRMATION => 'onEmailSentShippingConfirmation',

        ];
    }

    public function onCartCreate(OrderEvent $event): void
    {
        /** Put orderId into session */
        $this->session->set('orderId', $event->getSubject()->getId());

        $message = 'First product was added to the cart.';
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());

        $this->setOrderLog($order, $message, null, $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
    }

    public function onOrderCreate(OrderEvent $event): void
    {
//        /** Put orderId into session */
//        $this->session->set('orderId', $event->getSubject()->getId());

        /** On order creation generate and assign automatically an Order Number */
        /** @var Order $order */
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());

        if ($event->hasArgument('status')) {
            $shortcode = $event->getArgument('status');
        }
        if (!isset($shortcode)) {
            throw new Exception('STUPID: OrderSubscriber >> onOrderCreate() függvényben >> nincs \'statusShortcode\' definiálva!');
        }

        $messages = [
            'created' => '{{fullname}} placed this order.',
            'fulfilled' => 'A rendelés teljesítve.',
//                'The order was successfully fulfilled and closed.',
            'rejected' => 'The order was rejected.',
            OrderStatus::ORDER_CANCELED => 'Rendelés törölve.'
//                'The order was deleted.',
        ];

        switch ($shortcode) {
            case OrderStatus::ORDER_CREATED:
                $message = $this->translator->trans($messages[$shortcode], [
                    '{{fullname}}' => $order->getFullname(),
                ]);
                break;
            case OrderStatus::STATUS_FULFILLED || OrderStatus::ORDER_REJECTED || OrderStatus::ORDER_CANCELED:
                $message = $this->translator->trans($messages[$shortcode], []);
                break;
//            case OrderStatus::ORDER_REJECTED:
//                $message = $this->translator->trans($messages[$shortcode], []);
//                break;
        }

        $this->setOrderLog($order, $message, null, $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
    }

    public function onDeliveryDateUpdate(OrderEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();

        if (!$event->getArgument('channel')) {
            throw new Exception('STUPID: OrderSubscriber >> onDeliveryDateUpdate() függvényben >> nincs \'channel\' definiálva!');
        }

        $message = 'Szállítási idő rögzítve: {{date}}, {{interval}} óra között.';
        $message = $this->translator->trans($message, [
            '{{date}}' => $order->getDeliveryDate()->format('Y-m-d'),
            '{{interval}}' => $order->getDeliveryInterval(),
        ]);

        $this->setOrderLog($order, $message, null, $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
    }

    public function onPaymentStatusUpdate(OrderEvent $event): void
    {
        /** @var Order $order */
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());

        $shortcode = $event->getArgument('status');
        if (!isset($shortcode)) {
            throw new Exception('STUPID: OrderSubscriber >> onPaymentStatusUpdate() függvényben >> nincs \'statusShortcode\' definiálva!');
        }

        $messages = [
//            'pending' => 'A {{amount}} payment is pending on {{payment}}.',
//            'success' => '',
//            'failure' => '',
//            'error' => '',
//            'canceled' => '',
//
//            'paid' => 'A {{amount}} payment was processed on {{payment}}.',
//            'partially_paid' => 'A partial payment of {{amount}} has been paid by the customer.',
//            'partially_refunded' => 'A partial refund of {{amount}} has been issue to the customer.',
//            'refunded' => 'A {{amount}} has been refunded to the customer.',

            'pending' => 'A {{amount}} payment is pending on {{payment}}.',
            'paid' => 'A {{amount}} payment was processed on {{payment}}.',
            'partially_paid' => 'A partial payment of {{amount}} has been paid by the customer.',
            'partially_refunded' => 'A partial refund of {{amount}} has been issue to the customer.',
            'refunded' => 'A {{amount}} has been refunded to the customer.',
        ];
        $descriptions = [
//            'pending' => $this->twig->render('admin/order/_history-pending.html.twig', [
//                'order' => $order,
//            ]),
//            'paid' => $this->twig->render('admin/order/_history-paid.html.twig', [
//                'order' => $order,
//            ]),
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
                $description = $this->twig->render('admin/order/_history-pending.html.twig', [
                    'order' => $order,
//                    'paymentGateway' => $order->getPaymentMethod()->getShortcode(),
                ]);
                break;
            case PaymentStatus::STATUS_PAID:
                $message = $this->translator->trans($messages[$shortcode], [
                    '{{amount}}' => $money($order->getSummary()->getTotalAmountToPay()),
                    '{{payment}}' => $order->getPaymentMethod(),
                ]);
                $description = $this->twig->render('admin/order/_history-paid.html.twig', [
                    'order' => $order,
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

        $this->setOrderLog($order, $message, $description, $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
    }

    public function onEmailSentOrderConfirmation(OrderEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();

        if (!$event->getArgument('channel')) {
            throw new Exception('STUPID: OrderSubscriber >> onDeliveryDateUpdate() függvényben >> nincs \'channel\' definiálva!');
        }

//        $t = 'Order confirmation email was sent to Liviu jr. Chioran (liviu.chioran@gmail.com).';
        $message = 'A rendelést visszaigazoló email kiküldve: {{ recipientName }} ({{ recipientEmail }})';
        $message = $this->translator->trans($message, [
            '{{ recipientName }}' => $order->getFullname(),
            '{{ recipientEmail }}' => $order->getEmail(),
        ]);

        $this->setOrderLog(
            $order,
            $message,
            null,
            $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')])
        );
    }

    public function onEmailSentShippingConfirmation(OrderEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();

        if (!$event->getArgument('channel')) {
            throw new Exception('STUPID: OrderSubscriber >> onDeliveryDateUpdate() függvényben >> nincs \'channel\' definiálva!');
        }

//        $t = 'Order confirmation email was sent to Liviu jr. Chioran (liviu.chioran@gmail.com).';
        $message = 'A szállításról szóló email kiküldve: {{ recipientName }} ({{ recipientEmail }})';
        $message = $this->translator->trans($message, [
            '{{ recipientName }}' => $order->getFullname(),
            '{{ recipientEmail }}' => $order->getEmail(),
        ]);

        $this->setOrderLog(
            $order,
            $message,
            null,
            $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')])
        );
    }

    // Helper
    private function setOrderLog(Order $order, string $message, ?string $description = null, OrderLogChannel $channel)
    {
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

    public function onSetOrderAsTracked(OrderEvent $event): void
    {
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());

        $order->setIsConversionTracked(true);
        $this->em->persist($order);
        $this->em->flush();
    }
}