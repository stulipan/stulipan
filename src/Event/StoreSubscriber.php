<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Cart;
use App\Entity\Checkout;
use App\Entity\CheckoutItem;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderLog;
use App\Entity\OrderLogChannel;
use App\Services\StoreSessionStorage;
use App\Twig\AppExtension;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class StoreSubscriber implements EventSubscriberInterface
{

    private $storage;
    private $em;
    private $translator;
    private $eventDispatcher;
    private $appExtension;
    private $twig;

    public function __construct(StoreSessionStorage $storage, EntityManagerInterface $em,
                                TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher,
                                AppExtension $appExtension, Environment $twig
                                )
    {
        $this->storage = $storage;
        $this->em = $em;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->appExtension = $appExtension;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StoreEvent::CART_CREATE => 'onCartCreate',
            StoreEvent::CART_UPDATE => 'onCartUpdate',
            StoreEvent::CHECKOUT_CREATE => 'onCheckoutCreate',
            StoreEvent::CHECKOUT_UPDATE => 'onCheckoutUpdate',
            StoreEvent::ORDER_CREATE => 'onOrderCreate',
            StoreEvent::ORDER_UPDATE => 'onOrderUpdate',
            StoreEvent::ORDER_TRACK_CONVERSION => 'setOrderAsTracked',

            StoreEvent::IMPORT_ITEMS_FROM_CART => 'importItemsFromCart',
            StoreEvent::IMPORT_ITEMS_FROM_CHECKOUT => 'importItemsFromCheckout',

            StoreEvent::EMAIL_SEND_ORDER_CONFIRMATION => 'onEmailSentOrderConfirmation',
            StoreEvent::EMAIL_SEND_SHIPPING_CONFIRMATION => 'onEmailSentShippingConfirmation',
            StoreEvent::CUSTOMER_CREATE => 'onCustomerCreate',
            StoreEvent::CUSTOMER_UPDATE => 'onCustomerUpdate',
        ];
    }

    public function onCartCreate(StoreEvent $event): void
    {
        /** Put cartId into session */
        $this->storage->set(StoreSessionStorage::CART_ID, $event->getSubject()->getId());

//        $message = 'First product was added to the cart.';
//        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());
//        $this->setOrderLog($order, $message, null, $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $event->getArgument('channel')]));
    }

    public function onCartUpdate(StoreEvent $event): void
    {
        /** @var Cart $cart */
        $cart = $event->getSubject();

        $checkout = $cart->getCheckout();
        if (!$checkout) {
            return;
        }
        $this->runEvent(StoreEvent::IMPORT_ITEMS_FROM_CART, $checkout);
        $this->em->persist($checkout);

        $order = $checkout->getOrder();
        if ($order) {
            $this->runEvent(StoreEvent::IMPORT_ITEMS_FROM_CHECKOUT, $order);
            $this->em->persist($order);
        }
        $this->em->flush();
    }

    public function onCheckoutCreate(StoreEvent $event): void
    {
        $this->storage->set(StoreSessionStorage::CHECKOUT_ID, $event->getSubject()->getId());
    }

    public function onCheckoutUpdate(StoreEvent $event): void
    {
        /** @var Checkout $checkout */
        $checkout = $event->getSubject();

//        $this->runEvent(StoreEvent::CUSTOMER_UPDATE, $checkout, $event->getArguments());
    }

    public function importItemsFromCart(StoreEvent $event): void
    {
        /** @var Cart $cart */
        $cart = $this->storage->getCartById();
        /** @var Checkout $checkout */
        $checkout = $event->getSubject();

        // update the Checkout with the items from the Cart
        try {
            $checkout->importItemsFromCart($cart, $this->translator->trans('cart.product.not-enough-stock'));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $checkout->setMessage($cart->getMessage());
        $checkout->setMessageAuthor($cart->getMessageAuthor());
    }

    public function importItemsFromCheckout(StoreEvent $event): void
    {
        /** @var Checkout $checkout */
        $checkout = $this->storage->getCheckoutById();
        /** @var Order $order */
        $order = $event->getSubject();

        // update the Order with the items from the Checkout. Note that items in an Order can change only when Cart items change, which is why we update Order here!
        try {
            $order->importItemsFromCheckout($checkout, $this->translator->trans('cart.product.not-enough-stock'));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $order->setMessage($checkout->getMessage());
        $order->setMessageAuthor($checkout->getMessageAuthor());
    }

    public function onOrderCreate(StoreEvent $event): void
    {
        $this->storage->set(StoreSessionStorage::ORDER_ID, $event->getSubject()->getId());

        $orderId = $event->getSubject()->getId();
        $channelShortcode = $event->getArgument('channel');

        $order = $this->em->getRepository(Order::class)->find($orderId);
        $channel = $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $channelShortcode]);
        $message = '{{fullname}} leadta a rendelést.'; // '{{fullname}} placed this order.'

        $this->setOrderLog($order, $message, null, $channel);
    }

    public function onOrderUpdate(StoreEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();

        $channel = null;
        $message = null;
        $messageKey = null;

        if ($event->hasArgument('channel')) {
            $channelShortcode = $event->getArgument('channel');
            $channel = $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $channelShortcode]);
        }

        if ($event->hasArgument('newPaymentStatus')) {
            $paymentStatusShortcode = $event->getArgument('newPaymentStatus');
            $messageKey = $paymentStatusShortcode;
        }

        $messages = [
//            'pending' => 'A {{amount}} payment is pending on {{paymentGateway}}.',
//            'paid' => 'A {{amount}} payment was processed on {{paymentGateway}}.',
            'pending' => '{{amount}} összeg vár fizetésre a {{paymentGateway}}.',
            'paid' => '{{amount}} kifizetés sikeresen feldolgozásra került ({{paymentGateway}}).',
            'partially_paid' => 'A partial payment of {{amount}} has been paid by the customer.',
            'partially_refunded' => 'A partial refund of {{amount}} has been issue to the customer.',
            'refunded' => 'A {{amount}} has been refunded to the customer.',
        ];

        $message = $this->translator->trans($messages[$messageKey], [
            '{{amount}}' => $this->appExtension->formatMoney($order->getTotalAmountToPay()),
            '{{paymentGateway}}' => $order->getPaymentMethod(),
        ]);
        $description = $this->twig->render('admin/order/_history-pending.html.twig', [
            'order' => $order,
        ]);

        $this->setOrderLog($order, $message, $description, $channel);
    }

    public function onEmailSentOrderConfirmation(StoreEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();
        $channel = null;

        if (!$event->getArgument('channel')) {
            throw new Exception('STUPID: StoreSubscriber >> onEmailSentOrderConfirmation() függvényben >> nincs \'channel\' definiálva!');
        }

        if ($event->hasArgument('channel')) {
            $channelShortcode = $event->getArgument('channel');
            $channel = $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $channelShortcode]);
        }

        $message = 'A rendelést visszaigazoló email kiküldve: {{recipientName}} ({{recipientEmail}})';
        $message = $this->translator->trans($message, [
            '{{recipientName}}' => $order->getFullname(),
            '{{recipientEmail}}' => $order->getEmail(),
        ]);

        $this->setOrderLog($order, $message, null, $channel);
    }

    public function onEmailSentShippingConfirmation(StoreEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();
        $channel = null;

        if (!$event->getArgument('channel')) {
            throw new Exception('STUPID: StoreSubscriber >> onEmailSentShippingConfirmation() függvényben >> nincs \'channel\' definiálva!');
        }

        if ($event->hasArgument('channel')) {
            $channelShortcode = $event->getArgument('channel');
            $channel = $this->em->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => $channelShortcode]);
        }

        $message = 'A szállításról szóló email kiküldve: {{recipientName}} ({{recipientEmail}})';
        $message = $this->translator->trans($message, [
            '{{recipientName}}' => $order->getFullname(),
            '{{recipientEmail}}' => $order->getEmail(),
        ]);

        $this->setOrderLog($order, $message, null, $channel);
    }

    public function onCustomerCreate(StoreEvent $event): void
    {
        /** @var Checkout $checkout */
        $checkout = $event->getSubject();

//        /** @var Customer $customer */
//        $customer = $checkout->getCustomer();

        // Search the database for a Customer with this email address
        $email = $checkout->getEmail(); // This is equivalent to $email = $customer->getEmail();
        $customerInDatabase = $this->em->getRepository(Customer::class)->findOneBy(['email' => $email]);

        // Update old Customer with info provided in the event
        if (isset($customerInDatabase) && $customerInDatabase) {
            /** @var Customer $customer */
            $customer = $customerInDatabase;
        }
        // Else create new Customer with info provided in the event
        else {
            $customer = new Customer();
            $customer->setEmail($email);
        }

        if ($event->hasArgument('acceptsMarketing')) {
            $customer->setAcceptsMarketing($event->getArgument('acceptsMarketing'));
        }

        // Update optin date only when isAcceptsMarketing == true
        if ($customer->isAcceptsMarketing()) {
            $customer->setAcceptsMarketingUpdatedAt(new DateTime('now'));
            $customer->setMarketingOptinLevel(Customer::OPTIN_LEVEL_SINGLE_OPTIN);
        }

        $checkout->setCustomer($customer);
        $customer->addCheckout($checkout);

        $this->em->persist($customer);
        $this->em->persist($checkout); /// lehet nem kelll...
        $this->em->flush();

//        if ($customer) {
//            if ($customer->getCheckouts()->count() == 1) {  // 1 meaning the current checkout
//                $this->em->remove($checkout->getCustomer());
//                $this->em->flush();
//            }
//        }
    }

    public function onCustomerUpdate(StoreEvent $event): void
    {
        /** @var Customer $customer */
        $customer = $event->getSubject()->getCustomer();

        if ($event->hasArgument('acceptsMarketing')) {
            $customer->setAcceptsMarketing($event->getArgument('acceptsMarketing'));
        }

        // Update optin date only when isAcceptsMarketing == true
        if ($customer->isAcceptsMarketing()) {
            $customer->setAcceptsMarketingUpdatedAt(new DateTime('now'));
            $customer->setMarketingOptinLevel(Customer::OPTIN_LEVEL_SINGLE_OPTIN);
        }

        $this->em->persist($customer);
        $this->em->flush();
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

    public function setOrderAsTracked(StoreEvent $event): void
    {
        $order = $this->em->getRepository(Order::class)->find($event->getSubject()->getId());
        $order->setIsConversionTracked(true);

        $this->em->persist($order);
        $this->em->flush();
    }

    private function runEvent(string $eventName, &$subject, array $arguments = []) {
        $arguments['channel'] = OrderLog::CHANNEL_CHECKOUT;

        $event = new StoreEvent($subject, $arguments);
        $event = $this->eventDispatcher->dispatch($event, $eventName);
        return $event->getSubject();
    }
}