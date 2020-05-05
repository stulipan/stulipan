<?php

namespace App\Controller\Shop;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Address;
use App\Entity\CardCategory;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Geo\GeoCountry;
use App\Entity\Model\GeneratedDates;
use App\Entity\Model\DeliveryDateWithIntervals;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\Model\CartCard;
use App\Entity\Model\MessageAndCustomer;
use App\Entity\Model\DeliveryDate;

use App\Entity\Order;
use App\Entity\OrderBuilder;
use App\Entity\OrderLog;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Product\ProductCategory;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\Shipping;
use App\Entity\Payment;

use App\Entity\Model\CustomerBasic;
use App\Entity\User;
use App\Event\OrderEvent;
use App\Form\CartHiddenDeliveryDateFormType;
use App\Form\CartSelectDeliveryDateFormType;
use App\Form\MessageAndCustomerFormType;
use App\Form\RecipientType;
use App\Form\SenderType;
use App\Form\SetDiscountType;

use App\Form\ShipAndPayFormType;
use App\Form\UserBasicDetailsFormType;
use App\Form\UserRegistrationFormType;
use App\Services\Settings;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Annotation\Route;


class CheckoutController extends AbstractController
{
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;

    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * @Route("/rendeles/ajandek", name="site-checkout-step1-pickExtraGift")
     */
    public function checkoutStep1PickExtraGift(Settings $settings)
    {
        $orderBuilder = $this->orderBuilder;
        $customer = $this->getUser();

        $setDiscountForm = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());

        $giftCategory = $this->getDoctrine()->getRepository(ProductCategory::class)
            ->find($settings->get('general.giftCategory'));
        $extras = $giftCategory->getProducts();

        $card = new CartCard($orderBuilder->getCurrentOrder()->getMessage(), $orderBuilder->getCurrentOrder()->getMessageAuthor());
        $cardCategories = $this->getDoctrine()->getRepository(CardCategory::class)
            ->findAll();

        /** If Customer exists (is logged in), add current user as Customer (to the current Order) */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
        }
        /** Else, create form for basic details */
        else {
            if ($orderBuilder->getCustomer()) {
                $customer = $orderBuilder->getCustomer();
            }
        }
        $customerBasic = new CustomerBasic(
            $customer->getEmail() ?? $orderBuilder->getCurrentSession()->fetch('email'),
            $customer->getFirstname() ?? $orderBuilder->getCurrentSession()->fetch('firstname'),
            $customer->getLastname() ?? $orderBuilder->getCurrentSession()->fetch('lastname'),
            $customer->getPhone() ?? $orderBuilder->getCurrentOrder()->getBillingPhone()
        );
        $messageAndCustomer = new MessageAndCustomer($card, $customerBasic);
        $messageAndCustomerForm = $this->createForm(MessageAndCustomerFormType::class, $messageAndCustomer);


        if (!$orderBuilder->hasItems()) {
            return $this->render('webshop/cart/checkout-step1-extraGifts.html.twig', [
                'title' => 'Kosár',
                'order' => $orderBuilder,
                'orderId' => $orderBuilder->getCurrentOrder()->getId(),
                'setDiscountForm' => $setDiscountForm->createView(),
                'products' => $extras,
                'giftCategory' => $giftCategory,
                'progressBar' => 'pickExtraGift',
                'messageAndCustomerForm' => isset($messageAndCustomerForm) ? $messageAndCustomerForm->createView() : null,
                'cardCategories' => $cardCategories,
                'showQuantity' => true,
            ]);
        }

        return $this->render('webshop/cart/checkout-step1-extraGifts.html.twig', [
            'title' => 'Kosár',
            'order' => $orderBuilder,
            'orderId' => $orderBuilder->getCurrentOrder()->getId(),
            'setDiscountForm' => $setDiscountForm->createView(),
//            'itemsInCart' => $orderBuilder->countItems(),
//            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
            'progressBar' => 'pickExtraGift',
            'products' => $extras,
            'giftCategory' => $giftCategory,
            'messageAndCustomerForm' => isset($messageAndCustomerForm) ? $messageAndCustomerForm->createView() : null,
            'cardCategories' => $cardCategories,
            'showQuantity' => true,
        ]);
    }


    /**
     * @Route("/rendeles/cimzett", name="site-checkout-step2-pickRecipient")
     */
    public function checkoutStep2PickRecipient()
    {
        /**
         * Ezt be kell szúrni a services.yaml-ba
         * App\Entity\OrderBuilder:
         *    public: true
         */
        $orderBuilder = $this->orderBuilder;
    
        $validation = $this->validateOrderAtStep($orderBuilder, 1);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $shippingMethods = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAllOrdered();

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

        $selectedDate = null === $orderBuilder->getCurrentOrder()->getDeliveryDate() ? null : $orderBuilder->getCurrentOrder()->getDeliveryDate();
        $selectedInterval = null === $orderBuilder->getCurrentOrder()->getDeliveryInterval() ? null : $orderBuilder->getCurrentOrder()->getDeliveryInterval();
        $selectedIntervalFee = null === $orderBuilder->getCurrentOrder()->getDeliveryFee() ? null : $orderBuilder->getCurrentOrder()->getDeliveryFee();

        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval, $selectedIntervalFee);
        $hiddenDateForm = $this->createForm(CartHiddenDeliveryDateFormType::class,$hiddenDates);

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

        $customer = $this->getUser();  // equivalent of: $customer = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * After login, add current user as Customer (to the current Order and to the current Recipient also)
         */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /**
             * If before login a Recipient was added to the Order, asign the current Customer to this Recipient
             */
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
        if ($customer) {
            $recipients = $customer->getRecipients();
        }
        /**
         *  Else, simply return the Recipiernt/Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $recipients = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getRecipient()) {
                $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
            }
        }

        if ($recipients->isEmpty()) {
            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
            $recipientForm = $this->createForm(RecipientType::class, $recipient);

            return $this->render('webshop/cart/checkout-step2-pickRecipient.html.twig', [
                'title' => 'Kinek küldöd?',
                'order' => $orderBuilder,
                'shippingMethods' => $shippingMethods,
                'recipientForm' => $recipientForm->createView(),
//                'dateForm' => $dateForm->createView(),
                'progressBar' => 'pickRecipient',
                'generatedDates' => $generatedDates,
                'hiddenDateForm' => $hiddenDateForm->createView(),
                'selectedDate' => $selectedDate,
                'selectedInterval' => $selectedInterval,
            ]);
        }

        return $this->render('webshop/cart/checkout-step2-pickRecipient.html.twig', [
            'title' => 'Kinek küldöd?',
            'order' => $orderBuilder,
            'shippingMethods' => $shippingMethods,
            'recipients' => $recipients,
            'selectedRecipient' => null !== $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
//            'dateForm' => $dateForm->createView(),
            'progressBar' => 'pickRecipient',
            'generatedDates' => $generatedDates,
            'hiddenDateForm' => $hiddenDateForm->createView(),
            'selectedDate' => $selectedDate,
            'selectedInterval' => $selectedInterval,
        ]);
    }

    /**
     * @Route("/rendeles/fizetes", name="site-checkout-step3-pickPayment")
     */
    public function checkoutStep3PickPayment()
    {
        $orderBuilder = $this->orderBuilder;
    
        $validation = $this->validateOrderAtStep($orderBuilder, 2);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $checkoutForm = $this->createForm(ShipAndPayFormType::class, $orderBuilder->getCurrentOrder());
        $user = new User();
        $user->setEmail($orderBuilder->getCurrentSession()->fetch('email'));
        $user->setFirstname($orderBuilder->getCurrentSession()->fetch('firstname'));
        $user->setLastname($orderBuilder->getCurrentSession()->fetch('lastname'));
        $registrationForm = $this->createForm(UserRegistrationFormType::class, $user);

        $shippingMethods = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAllOrdered();
        $paymentMethods = $this->getDoctrine()
            ->getRepository(Payment::class)
            ->findAllOrdered();

        $customer = $this->getUser();  // equivalent of: $customer = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * After login, add current user as Customer (to the current Order and to the current Recipient also)
         */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /**
             * If before login a Sender was added to the Order, asign the current Customer to this Sender
             */
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
        if ($customer) {
            $senders = $customer->getSenders();
        }
        /**
         *  Else, simply return the Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $senders = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getSender()) {
                $senders->add($orderBuilder->getCurrentOrder()->getSender());
            }
        }

        if ($senders->isEmpty()) {
            $sender = new Sender();
            if ($customer) {
                $sender->setName($customer->getFullname());
            } else {
                $sender->setName($orderBuilder->getCurrentSession()->fetch('firstname').' '.$orderBuilder->getCurrentSession()->fetch('lastname'));
            }
            
            $sender->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
            $senderForm = $this->createForm(SenderType::class, $sender);

            return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
                'title' => 'Szállítás és fizetés',
                'order' => $orderBuilder,
                'form' => $checkoutForm->createView(),
                'shippingMethods' => $shippingMethods,
                'paymentMethods' => $paymentMethods,
                'senderForm' => $senderForm->createView(),
                'progressBar' => 'pickPayment',
                'registrationForm' => $registrationForm->createView(),
            ]);
        }

        return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
            'title' => 'Szállítás és fizetés',
            'order' => $orderBuilder,
            'form' => $checkoutForm->createView(),
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $paymentMethods,
            'senders' => $senders,
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
            $isBankTransfer = $order->getPayment()->isBankTransfer() ? true : false;
    
            return $this->render('webshop/cart/checkout-step4-thankyou.html.twig',[
                'title' => 'Sikeres rendelés!',
                'order' => $order,
                'progressBar' => 'thankyou',
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
    public function checkoutThankyou(EventDispatcherInterface $eventDispatcher)
    {
        $orderBuilder = $this->orderBuilder;
        $validation = $this->validateOrderAtStep($orderBuilder, 3);
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
//        $orderBuilder->getCurrentSession()->removeOrderFromSession();    /////  EZT LE KELL VENNI KOMMENTBOL HA VALOBAN EL AKAROM A RENDELEST POSZTOLNI
        
        return $this->forward('App\Controller\Shop\CheckoutController::checkoutPlaceOrder', [
            'order' => $order,
        ]);
    }


    /**
     * Checks if the Order is valid at a given step in the Checkout process.
     * Returns 'true' if it's valid.
     * This is executed when next step is loaded, either
     * directly by URL or by clicking Continue button.
     */
    public function validateOrderAtStep(OrderBuilder $orderBuilder, int $checkoutStep): array 
    {
        $validOrder = true;
        if ($checkoutStep == 1) {
//            return true;
            if (!$orderBuilder->hasItems()) {
                $validOrder = false;
                $this->addFlash('items-missing', 'A kosarad üres.');
            }
            if (!$orderBuilder->hasCustomer()) {
                $validOrder = false;
                // az alabbi sor nem szukseges, mert ha ide ugrik vissza akkor mar tuti van Customer adat.
//                $this->addFlash('customer-missing', 'Nem adtad meg az adataidat vagy valamelyik mezőt nem tölötted ki.');
            }
//            if (!$orderBuilder->hasMessage()) {
//                $validOrder = false;
//                $this->addFlash('message-warning', 'Ha szeretnél üzenni a virággal, itt tudod kifejezni pár szóban, mit írjunk az üdvözlőlapra. (Nem kötelező)');
//            }
            if ($validOrder) {
                return ['isValid' => true, 'route' => null];
            } else {
                return ['isValid' => false, 'route' => 'site-checkout-step1-pickExtraGift'];
            }
        }

        if ($checkoutStep == 2) {
            // If step1 is invalid, then no need to check step2. Return route to step1
            $validation = $this->validateOrderAtStep($orderBuilder, 1);
            if (!$validation['isValid']) {
                return ['isValid' => false, 'route' => $validation['route']];
//                return ['isValid' => false, 'route' => 'site-checkout-step1-pickExtraGift'];
            }
            // Continue normally and check step2
            if (!$orderBuilder->hasRecipient()) {
                $validOrder = false;
                $this->addFlash('recipient-missing', 'Nem adtál meg címzettet.');
            }
            if (!$orderBuilder->hasDeliveryDate()) {
                $validOrder = false;
                $this->addFlash('date-missing', 'Add meg a szállítás idejét! Bizonyosodj meg róla, hogy választottál szállítási idősávot is.');
            }
//            if ($orderBuilder->isDeliveryDateInPast()) {
//                $validOrder = false;
//                $this->addFlash('date-missing', 'Nem adtál meg szállítási napot! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
//            }

//            if (!$orderBuilder->hasShipping()) {
//                $validOrder = false;
//                $this->addFlash('shipping-missing', 'Válassz szállítási módot.');
//            }
            if ($validOrder) {
                return ['isValid' => true, 'route' => null];
            } else {
                return ['isValid' => false, 'route' => 'site-checkout-step2-pickRecipient'];
            }
    
        }

        if ($checkoutStep == 3) {
            // If step2 is invalid, then no need to check step3. Return route to step2
            $validation = $this->validateOrderAtStep($orderBuilder, 2);
            if (!$validation['isValid']) {
//                return ['isValid' => false, 'route' => 'site-checkout-step2-pickRecipient'];
                return ['isValid' => false, 'route' => $validation['route']];
            }
            // Continue normally and check step3
            if (!$orderBuilder->hasSender()) {
                $validOrder = false;
                $this->addFlash('sender-missing', 'Adj meg egy számlázási címet.');
            }
            if (!$orderBuilder->hasPayment()) {
                $validOrder = false;
                $this->addFlash('payment-missing', 'Válassz fizetési módot.');
            }
            if (!$orderBuilder->hasShipping()) {
                $validOrder = false;
                $this->addFlash('shipping-missing', 'Válassz szállítási módot.');
            }
    
            if ($validOrder) {
                return ['isValid' => true, 'route' => null];
            } else {
                return ['isValid' => false, 'route' => 'site-checkout-step3-pickPayment'];
            }
        }
    }

}