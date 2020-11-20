<?php

namespace App\Controller\Shop;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Address;
use App\Entity\GreetingCardMessageCategory;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Geo\GeoCountry;
use App\Entity\Model\GeneratedDates;
use App\Entity\Model\DeliveryDateWithIntervals;
use App\Entity\Model\HiddenDeliveryDate;
use App\Model\CartGreetingCard;
use App\Entity\Model\DeliveryDate;

use App\Model\CheckoutPaymentMethod;
use App\Model\CheckoutShippingMethod;
use App\Entity\Order;
use App\Entity\OrderBuilder;
use App\Entity\OrderLog;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Product\ProductCategory;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\ShippingMethod;
use App\Entity\PaymentMethod;

use App\Entity\Model\CustomerBasic;
use App\Entity\User;
use App\Event\OrderEvent;
use App\Form\DeliveryDate\CartHiddenDeliveryDateFormType;
use App\Form\DeliveryDate\CartSelectDeliveryDateFormType;
use App\Form\Checkout\PaymentMethodType;
use App\Form\CustomerBasic\CustomerBasicsFormType;
use App\Form\GreetingCard\GreetingCardFormType;
use App\Form\RecipientType;
use App\Form\SenderType;
use App\Form\SetDiscountType;

use App\Form\Checkout\ShippingMethodType;
use App\Form\UserRegistration\UserRegistrationFormType;
use App\Services\CheckoutSettings;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;


class CheckoutController extends AbstractController
{
    private const STEP_CART = 'cart';
    private const STEP_DELIVERY_ADDRESS = 'delivery-address';
    private const STEP_SHIPPING_METHOD = 'shipping-method';
    private const STEP_PAYMENT_METHOD = 'payment-method';

    /**
     * @var OrderBuilder
     */
    private $orderBuilder;

    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * @Route("/rendeles/ajandek", name="site-checkout-step0-pickExtraGift")
     */
    public function checkoutStep0PickExtraGift(StoreSettings $settings)
    {
        $orderBuilder = $this->orderBuilder;
        $customer = $this->getUser();

        $setDiscountForm = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());

        $giftCategory = $this->getDoctrine()->getRepository(ProductCategory::class)->find($settings->get('general.giftCategory'));
        $extras = $giftCategory->getProducts();

        $greetingCard = new CartGreetingCard($orderBuilder->getCurrentOrder()->getMessage(), $orderBuilder->getCurrentOrder()->getMessageAuthor());
        $greetingCardForm = $this->createForm(GreetingCardFormType::class, $greetingCard);

        $cardCategories = $this->getDoctrine()->getRepository(GreetingCardMessageCategory::class)
            ->findAll();

        /** If Customer exists (is logged in), add the current user as Customer (to the current Order) */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
        }
        /** Else, create form for basic details */
        else {
            if ($orderBuilder->getCustomer()) {
                $customer = $orderBuilder->getCustomer();
            }
        }

        if (!$orderBuilder->hasItems()) {
            return $this->render('webshop/cart/checkout-step0-extraGifts.html.twig', [
                'title' => 'Kosár',
                'order' => $orderBuilder->getCurrentOrder(),
                'orderId' => $orderBuilder->getCurrentOrder()->getId(),
                'setDiscountForm' => $setDiscountForm->createView(),
                'products' => $extras,
                'giftCategory' => $giftCategory,
                'progressBar' => 'pickExtraGift',
                'greetingCardForm' => $greetingCardForm->createView(),
                'cardCategories' => $cardCategories,
            ]);
        }

        return $this->render('webshop/cart/checkout-step0-extraGifts.html.twig', [
            'title' => 'Kosár',
            'order' => $orderBuilder->getCurrentOrder(),
            'orderId' => $orderBuilder->getCurrentOrder()->getId(),
            'setDiscountForm' => $setDiscountForm->createView(),
            'progressBar' => 'pickExtraGift',
            'products' => $extras,
            'giftCategory' => $giftCategory,
            'greetingCardForm' => $greetingCardForm->createView(),
            'cardCategories' => $cardCategories,
        ]);
    }

    /**
     * @Route("/rendeles/cimzett", name="site-checkout-step1-pickDeliveryAddress")
     */
    public function step1PickDeliveryAddress()
    {
        /**
         * Ezt be kell szúrni a services.yaml-ba
         * App\Entity\OrderBuilder:
         *    public: true
         */
        $orderBuilder = $this->orderBuilder;
        $validation = $this->validatePreviousStep($orderBuilder, self::STEP_CART);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $customer = $this->getUser();
        /** After login, add current user as Customer (to the current Order and to the current Recipient also) */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /** If before login a Recipient was added to the Order, assign the current Customer to this Recipient */
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($recipientInOrder);
                $entityManager->flush();
            }
        }

        /**
         * If Customer exists (is logged in), get all its Recipients and Senders
         */
        $recipient = null;
        if ($customer) {
            $recipients = $customer->getRecipients();

            if ($orderBuilder->hasRecipient()) {
                $recipient = $orderBuilder->getCurrentOrder()->getRecipient();
            }
//            else {
//                if ($customer->hasRecipients()) {
//                    if ($customer->getLastOrder()) {
//                        $recipient = $customer->getLastOrder()->getRecipient();
//                    } else {
//                        $recipient = $customer->getRecipients()->last();
//                    }
//                }
//            }
        }
        /**
         *  Else, simply return the Recipient/Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $recipients = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getRecipient()) {
                $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
            }
            if ($orderBuilder->hasRecipient()) {
                $recipient = $orderBuilder->getCurrentOrder()->getRecipient();
                $orderBuilder->setRecipient($recipient);
            }
        }
        $customerBasic = new CustomerBasic(
            $customer && $customer->getEmail() ? $customer->getEmail() : $orderBuilder->getCurrentSession()->fetch('email'),
            $customer && $customer->getFirstname() ? $customer->getFirstname() : $orderBuilder->getCurrentSession()->fetch('firstname'),
            $customer && $customer->getLastname() ? $customer->getLastname() : $orderBuilder->getCurrentSession()->fetch('lastname'),
            $customer && $customer->getPhone() ? $customer->getPhone() : $orderBuilder->getCurrentOrder()->getBillingPhone()
        );
//        dd($customer->getPhone());
//        dd($customerBasic->getPhone());

//        if ($recipients->isEmpty()) {
        if (!$recipient) {
            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
        }

        return $this->render('webshop/cart/checkout-step1-pickDeliveryAddress.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'recipients' => $recipients,
            'recipientForm' => $this->createForm(RecipientType::class, $recipient)->createView(),
            'selectedRecipient' => null !== $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
            'progressBar' => 'pickDeliveryAddress',
            'customerForm' => $this->createForm(CustomerBasicsFormType::class, $customerBasic)->createView(),
        ]);
    }

    /**
     * @Route("/rendeles/szallitas", name="site-checkout-step2-pickShipping")
     */
    public function step2PickShipping()
    {
        $orderBuilder = $this->orderBuilder;
        $validation = $this->validatePreviousStep($orderBuilder, self::STEP_DELIVERY_ADDRESS);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }
        $shippingMethods = $this->getDoctrine()->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']);

        $customer = $this->getUser();
        /** After login, add current user as Customer (to the current Order and to the current Recipient also) */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /** If before login a Recipient was added to the Order, assign the current Customer to this Recipient */
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($recipientInOrder);
                $entityManager->flush();
            }
        }

        $selectedDate = null === $orderBuilder->getCurrentOrder()->getDeliveryDate() ? null : $orderBuilder->getCurrentOrder()->getDeliveryDate();
        $selectedInterval = null === $orderBuilder->getCurrentOrder()->getDeliveryInterval() ? null : $orderBuilder->getCurrentOrder()->getDeliveryInterval();

        return $this->render('webshop/cart/checkout-step2-pickShipping.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'generatedDates' => $this->generateDates(),
            'hiddenDateForm' => $this->createHiddenDateForm()->createView(),
            'selectedDate' => $selectedDate,
            'selectedInterval' => $selectedInterval,

            'shippingMethods' => $shippingMethods,
            'shippingMethodForm' => $this->createForm(ShippingMethodType::class, (new CheckoutShippingMethod($orderBuilder->getCurrentOrder()->getShippingMethod())))->createView(),
//            'hasShipping' => $orderBuilder->getCurrentOrder()->getShippingMethod() ? 'true' : 'false',
            'progressBar' => 'pickShipping',
        ]);
    }

    /**
     * @Route("/rendeles/fizetes", name="site-checkout-step3-pickPayment")
     */
    public function step3PickPayment()
    {
        $orderBuilder = $this->orderBuilder;
    
        $validation = $this->validatePreviousStep($orderBuilder, self::STEP_SHIPPING_METHOD);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $user = new User();
        $user->setEmail($orderBuilder->getCurrentSession()->fetch('email'));
        $user->setFirstname($orderBuilder->getCurrentSession()->fetch('firstname'));
        $user->setLastname($orderBuilder->getCurrentSession()->fetch('lastname'));
        $registrationForm = $this->createForm(UserRegistrationFormType::class, $user);

        $shippingMethods = $this->getDoctrine()->getRepository(ShippingMethod::class)->findAll();
        $paymentMethods = $this->getDoctrine()->getRepository(PaymentMethod::class)->findAllOrdered();

        $customer = $this->getUser();  // equivalent of: $customer = $this->get('security.token_storage')->getToken()->getUser();

        /** After login, add current user as Customer (to the current Order and to the current Recipient also) */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /** If before login a Sender was added to the Order, asign the current Customer to this Sender */
            $senderInOrder = $orderBuilder->getCurrentOrder()->getSender();
            if ($senderInOrder) {
                $senderInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($senderInOrder);
                $entityManager->flush();
            }
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($recipientInOrder);
                $entityManager->flush();
            }
        }

        /**
         * If Customer exists (is logged in), get all its Senders
         */
        $sender = null;
        if ($customer) {
            $senders = $customer->getSenders();

            if ($orderBuilder->hasSender()) {
                $sender = $orderBuilder->getCurrentOrder()->getSender();
            }
//            else {
//                if ($customer->hasSenders()) {
//                    if ($customer->getLastOrder()) {
//                        $sender = $customer->getLastOrder()->getSender();
//                    } else {
//                        $sender = $customer->getSenders()->last();
//                    }
//                }
//            }
        }
        /**
         *  Else, simply return the Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $senders = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getSender()) {
                $senders->add($orderBuilder->getCurrentOrder()->getSender());
            }
            if ($orderBuilder->hasSender()) {
                $sender = $orderBuilder->getCurrentOrder()->getSender();
                $orderBuilder->setSender($sender);
            }
        }

//        if ($senders->isEmpty()) {
        if (!$sender) {
            $sender = new Sender();
            if ($customer) {
//                $sender->setName($customer->getFullname());
                $sender->setFirstname($customer->getFirstname());
                $sender->setLastname($customer->getLastname());
//                $sender->setPhone($customer->getPhone());
            } else {
                $sender->setFirstname($orderBuilder->getCurrentSession()->fetch('firstname'));
                $sender->setLastname($orderBuilder->getCurrentSession()->fetch('lastname'));
//                $sender->setPhone($orderBuilder->getCurrentSession()->fetch('phone'));
            }
            
            $sender->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
            $senderForm = $this->createForm(SenderType::class, $sender);

            return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
                'title' => 'Szállítás és fizetés',
//                'order' => $orderBuilder,
                'order' => $orderBuilder->getCurrentOrder(),
//                'form' => $checkoutForm->createView(),
                'shippingMethods' => $shippingMethods,
                'paymentMethods' => $paymentMethods,
                'hasShipping' => $orderBuilder->getCurrentOrder()->getShippingMethod() ? 'true' : 'false',
                'hasPayment' => $orderBuilder->getCurrentOrder()->getPaymentMethod() ? 'true' : 'false',
                'paymentMethodForm' => $this->createForm(PaymentMethodType::class, (new CheckoutPaymentMethod($orderBuilder->getCurrentOrder()->getPaymentMethod())))->createView(),
                'senders' => $senders,
                'senderForm' => $senderForm->createView(),
                'progressBar' => 'pickPayment',
                'registrationForm' => $registrationForm->createView(),
            ]);
        }

        return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
            'title' => 'Szállítás és fizetés',
//            'order' => $orderBuilder,
            'order' => $orderBuilder->getCurrentOrder(),
//            'form' => $checkoutForm->createView(),
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $paymentMethods,
            'hasShipping' => $orderBuilder->getCurrentOrder()->getShippingMethod() ? 'true' : 'false',
            'hasPayment' => $orderBuilder->getCurrentOrder()->getPaymentMethod() ? 'true' : 'false',
            'paymentMethodForm' => $this->createForm(PaymentMethodType::class, (new CheckoutPaymentMethod($orderBuilder->getCurrentOrder()->getPaymentMethod())))->createView(),
            'senders' => $senders,
            'sender' => $sender,
            'senderForm' => $this->createForm(SenderType::class, $sender)->createView(),
            'selectedSender' => null !== $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
            'progressBar' => 'pickPayment',
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * Ez csupán annyit csinál, hogy megjeleníti a Thank you oldal.
     *
     * @Route("/rendeles/leadas/{order}", name="site-checkout-place-order", methods={"POST"})
     */
    public function checkoutPlaceOrder(Order $order)
    {
        if ($order) {
            $isBankTransfer = $order->getPaymentMethod()->isBankTransfer() ? true : false;
    
            return $this->render('webshop/cart/checkout-step4-thankyou.html.twig',[
                'title' => 'Sikeres rendelés!',
                'order' => $order,
//                'progressBar' => 'thankyou',
                'isBankTransfer' => $isBankTransfer,
            ]);
        }
    }
    
    /**
     * Rögzíti a rendelést (azaz törli a sessionből) és tovább küld a 'checkoutPlaceOrder'-re,
     * ahol a Thankyou oldal megjelenítése történik.
     *
     * @Route("/rendeles/koszonjuk", name="site-checkout-step4-thankyou")
     */
    public function checkoutThankyou(EventDispatcherInterface $eventDispatcher, CheckoutSettings $checkoutSettings)
    {
        $testMode = $checkoutSettings->get('test-mode.test-mode');
        $orderBuilder = $this->orderBuilder;
        $validation = $this->validatePreviousStep($orderBuilder, self::STEP_PAYMENT_METHOD);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }
        
        $status = $this->getDoctrine()->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::STATUS_CREATED]);
        $paymentStatus = $this->getDoctrine()->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => PaymentStatus::STATUS_PENDING]);
        $orderBuilder->setStatus($status);
        $orderBuilder->setPaymentStatus($paymentStatus);
        $order = $orderBuilder->getCurrentOrder();

        $event = new OrderEvent($order, [
            'channel' => OrderLog::CHANNEL_CHECKOUT,
            'orderStatus' => $order->getStatus()->getShortcode(),
            'paymentStatus' => $order->getPaymentStatus()->getShortcode(),
        ]);
        $eventDispatcher->dispatch($event, OrderEvent::ORDER_CREATED);
        $eventDispatcher->dispatch($event, OrderEvent::PAYMENT_UPDATED);
        $eventDispatcher->dispatch($event, OrderEvent::DELIVERY_DATE_UPDATED);

        /** When at this step, the Order has been
         * successfully placed, so it can be removed from session */
        if (!$testMode) {
            $orderBuilder->getCurrentSession()->removeOrderFromSession();
        }

        return $this->forward('App\Controller\Shop\CheckoutController::checkoutPlaceOrder', [
            'order' => $order,
        ]);
    }


    /**
     * Checks if the Order is valid at a given step in the Checkout process.
     * Returns 'true' if it's valid.
     * This is executed when next step is loaded, either
     * directly by URL or by clicking Continue button.
     *
     * Steps:
     *          STEP_CART               = 'cart';
     *          STEP_DELIVERY_ADDRESS   = 'delivery-address';
     *          STEP_SHIPPING_METHOD    = 'shipping-method';
     *          STEP_PAYMENT_METHOD     = 'payment-method';
     */
    public function validatePreviousStep(OrderBuilder $orderBuilder, string $step): array
    {
        $validOrder = true;
        if ($step === self::STEP_CART) {
//            return true;
            if (!$orderBuilder->hasItems()) {
                $validOrder = false;
                $this->addFlash('items-missing', 'A kosarad üres.');
            } else {
                // Remove unavailable products from cart
                foreach ($orderBuilder->getItems() as $item) {
                    if (!$item->getProduct()->isPubliclyAvailable()) {
                        $orderBuilder->removeItem($item);
                    }
                }
            }
//            if (!$orderBuilder->hasMessage()) {
//                $validOrder = false;
//                $this->addFlash('message-warning', 'Ha szeretnél üzenni a virággal, itt tudod kifejezni pár szóban, mit írjunk az üdvözlőlapra. (Nem kötelező)');
//            }
            if ($validOrder) {
                return [
                    'isValid' => true,
                    'route' => null
                ];
            } else {
                return [
                    'isValid' => false,
                    'route' => 'site-checkout-step0-pickExtraGift'
                ];
            }
        }

        if ($step === self::STEP_DELIVERY_ADDRESS) {
            // If step1 is invalid, then no need to check step2. Return route to step1
            $validation = $this->validatePreviousStep($orderBuilder, self::STEP_CART);
            if ($validation['isValid'] == false) {
                return [
                    'isValid' => false,
                    'route' => $validation['route']
                ];
//                return ['isValid' => false, 'route' => 'site-checkout-step0-pickExtraGift'];
            }
            // Continue normally and check step2
            if (!$orderBuilder->hasRecipient()) {
                $validOrder = false;
                $this->addFlash('recipient-missing', 'Nem adtál meg címzettet.');
            }
            if (!$orderBuilder->hasCustomer()) {
                $validOrder = false;
                $this->addFlash('customer-missing', 'Nem adtad meg az adataidat vagy valamelyik mezőt nem tölötted ki.');
            }

            if ($validOrder) {
                return [
                    'isValid' => true,
                    'route' => null
                ];
            } else {
                return [
                    'isValid' => false,
                    'route' => 'site-checkout-step1-pickDeliveryAddress'
                ];
            }
        }

        if ($step === self::STEP_SHIPPING_METHOD) {
            // If previous step is invalid, then no need to check this step. Simply return route to previous step
            $validation = $this->validatePreviousStep($orderBuilder, self::STEP_DELIVERY_ADDRESS);
            if (!$validation['isValid']) {
                return [
                    'isValid' => false,
                    'route' => $validation['route']
                ];
//                return ['isValid' => false, 'route' => 'site-checkout-step0-pickExtraGift'];
            }
            // Continue normally and check this step
            if (!$orderBuilder->hasShippingMethod()) {
                $validOrder = false;
                $this->addFlash('shipping-missing', 'Válassz szállítási módot.');
            }
            if (!$orderBuilder->hasDeliveryDate()) {
                $validOrder = false;
                $this->addFlash('date-missing', 'Add meg a szállítás idejét! Bizonyosodj meg róla, hogy választottál szállítási idősávot is.');
            }
//            if ($orderBuilder->isDeliveryDateInPast()) {
//                $validOrder = false;
//                $this->addFlash('date-missing', 'Nem adtál meg szállítási napot! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
//            }
            if ($validOrder) {
                return ['isValid' => true, 'route' => null];
            } else {
                return ['isValid' => false, 'route' => 'site-checkout-step2-pickShipping'];
            }

        }

        if ($step === self::STEP_PAYMENT_METHOD) {
            // If previous step is invalid, then no need to check this step. Simply return route to previous step
            $validation = $this->validatePreviousStep($orderBuilder, self::STEP_SHIPPING_METHOD);
            if (!$validation['isValid']) {
//                return ['isValid' => false, 'route' => 'site-checkout-step1-pickDeliveryAddress'];
                return ['isValid' => false, 'route' => $validation['route']];
            }
            // Continue normally and check step3
            if (!$orderBuilder->hasSender()) {
                $validOrder = false;
                $this->addFlash('sender-missing', 'Adj meg egy számlázási címet.');
            }
            if (!$orderBuilder->hasPaymentMethod()) {
                $validOrder = false;
                $this->addFlash('payment-missing', 'Válassz fizetési módot.');
            }

            if ($validOrder) {
                return ['isValid' => true, 'route' => null];
            } else {
                return ['isValid' => false, 'route' => 'site-checkout-step3-pickPayment'];
            }
        }
    }

    /**
     * Generates the dates for the Delivery Date Picker.
     * @return GeneratedDates
     * @throws Exception
     */
    public function generateDates()
    {
        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new DateTime('+2 months'))->diff(new DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[] = (new DateTime('+'. ($i*24 + $offset).' hours'));
        }

        $generatedDates = new GeneratedDates();
        foreach ($dates as $date) {

            $specialDate = $this->getDoctrine()->getRepository(DeliverySpecialDate::class)
                ->findOneBy(['specialDate' => $date]);

            if (!$specialDate) {
                $dateType = $this->getDoctrine()->getRepository(DeliveryDateType::class)
                    ->findOneBy(['default' => DeliveryDateType::IS_DEFAULT]);
            } else {
                $dateType = $specialDate->getDateType();
            }
            $intervals = null === $dateType ? null : $dateType->getIntervals();

            $dateWithIntervals = new DeliveryDateWithIntervals();
            $dateWithIntervals->setDeliveryDate($date);
            $dateWithIntervals->setIntervals($intervals);
            $generatedDates->addItem($dateWithIntervals);
        }
        return $generatedDates;
    }

    /**
     * Creates the hidden form the Delivery Date Picker
     * @return FormInterface
     */
    public function createHiddenDateForm()
    {
        $order = $this->orderBuilder->getCurrentOrder();
        $selectedDate = null === $order->getDeliveryDate() ? null : $order->getDeliveryDate();
        $selectedInterval = null === $order->getDeliveryInterval() ? null : $order->getDeliveryInterval();
        $selectedIntervalFee = null === $order->getDeliveryFee() ? null : $order->getDeliveryFee();

        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval, $selectedIntervalFee);
        return $this->createForm(CartHiddenDeliveryDateFormType::class,$hiddenDates);
    }

}

//        if ($orderBuilder->getCurrentOrder()->getDeliveryDate()) {
//            $date = $orderBuilder->getCurrentOrder()->getDeliveryDate();
//            $stringInterval = $orderBuilder->getCurrentOrder()->getDeliveryInterval();
//
//            $interval = null;
//            if ($date) {
//                $specialDate = $this->getDoctrine()->getRepository(DeliverySpecialDate::class)
//                    ->findOneBy(['specialDate' => $date]);
//
//                if (!$specialDate) {
//                    $dateType = $this->getDoctrine()->getRepository(DeliveryDateType::class)
//                        ->findOneBy(['default' => DeliveryDateType::IS_DEFAULT]);
//                } else {
//                    $dateType = $specialDate->getDateType();
//                }
//                $intervals = null === $dateType ? null : $dateType->getIntervals()->getValues();
//
//                foreach ($intervals as $int) {
//                    if ($int->getName() === $stringInterval) {
//                        $interval = $int;
//                    }
//                }
//            }
//
//            $deliveryDate = new DeliveryDate($date,$interval);
//            $dateForm = $this->createForm(CartSelectDeliveryDateFormType::class, $deliveryDate);
//        } else {
//            $deliveryDate = new DeliveryDate(null, null);
//            $dateForm = $this->createForm(CartSelectDeliveryDateFormType::class, $deliveryDate);
//        }