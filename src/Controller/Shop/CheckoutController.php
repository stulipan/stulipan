<?php

namespace App\Controller\Shop;

use App\Controller\Utils\GeneralUtils;
use App\Entity\CardCategory;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Model\GeneratedDates;
use App\Entity\Model\DeliveryDateWithIntervals;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\Model\Message;
use App\Entity\Model\MessageAndCustomer;
use App\Entity\Model\DeliveryDate;

use App\Entity\OrderBuilder;
use App\Entity\Product\ProductCategory;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\Shipping;
use App\Entity\Payment;

use App\Entity\Model\CustomerBasic;
use App\Form\CartHiddenDeliveryDateFormType;
use App\Form\CartSelectDeliveryDateFormType;
use App\Form\MessageAndCustomerFormType;
use App\Form\RecipientType;
use App\Form\CheckoutFormType;
use App\Form\SenderType;
use App\Form\SetDiscountType;

use App\Form\UserBasicDetailsFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function checkoutStep1PickExtraGift()
    {
        $orderBuilder = $this->orderBuilder;
        $customer = $this->getUser();  // equivalent of: $customer = $this->get('security.token_storage')->getToken()->getUser();

        $setDiscountForm = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());

        $kategoria = $this->getDoctrine()->getRepository(ProductCategory::class)
            ->find(2);
        $extras = $kategoria->getProducts();


//        $basicUser = new CustomerBasic();
//        $customerForm = $this->createForm(CustomerBasicsFormType::class, $basicUser);

        $message = new Message($orderBuilder->getCurrentOrder()->getMessage(), $orderBuilder->getCurrentOrder()->getMessageAuthor());
        $customerBasic = new CustomerBasic(
            $orderBuilder->getCurrentSession()->fetch('email'),
            $orderBuilder->getCurrentSession()->fetch('firstname'),
            $orderBuilder->getCurrentSession()->fetch('lastname'),
            $orderBuilder->getCurrentOrder()->getBillingPhone()
        );
        $messageAndCustomer = new MessageAndCustomer($message, $customerBasic);
        $messageAndCustomerForm = $this->createForm(MessageAndCustomerFormType::class, $messageAndCustomer);
//        $messageAndCustomerForm->get('customer')->get('phone')->addError(new FormError('Hol a telszam?!'));
//        dd($messageAndCustomerForm->get('customer')->get('phone'));

        $cardCategories = $this->getDoctrine()->getRepository(CardCategory::class)
            ->findAll();

//        $cardMessages = [];
//        foreach ($cardCategories as $category) {
//            $cardMessages[$category->getName()] = $category->getMessages();
//            dd($cardCategories);
//        }

        if (!$orderBuilder->hasItems()) {
            return $this->render('webshop/cart/checkout-step1-extraGifts.html.twig', [
                'title' => 'Kosár',
                'order' => $orderBuilder,
                'setDiscountForm' => $setDiscountForm->createView(),
                'products' => $extras,
                'progressBar' => 'pickExtraGift',
                'customerDataForm' => isset($customerForm) ? $customerForm->createView() : null,
                'messageAndCustomerForm' => isset($messageAndCustomerForm) ? $messageAndCustomerForm->createView() : null,
                'cardCategories' => $cardCategories,
            ]);
        }

//        dd('wer');

        //ez CSAK akkor kell ha nem renderelem bele a template-be!!
//        $messageForm = $this->createForm(MessageType::class, $orderBuilder->getCurrentOrder());

        /**
         * After login, add current user as Customer (to the current Order)
         */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
        }

        /**
         * If Customer exists (is logged in), get all his basic details
         */
        if ($customer) {
//            $basicUser = new CustomerBasic();
//            $customerForm = $this->createForm(CustomerBasicsFormType::class, $basicUser);
//
//            $messageAndCustomer = new MessageAndCustomer();
            $messageAndCustomerForm = $this->createForm(MessageAndCustomerFormType::class, $messageAndCustomer);
        }
        /**
         *  Else, create form for basic details
         */
        else {
//            $basicUser = new CustomerBasic();
//            $customerForm = $this->createForm(CustomerBasicsFormType::class, $basicUser);
//
//            $messageAndCustomer = new MessageAndCustomer();
//            $messageAndCustomerForm = $this->createForm(MessageAndCustomerFormType::class, $messageAndCustomer);
        }

        return $this->render('webshop/cart/checkout-step1-extraGifts.html.twig', [
            'title' => 'Kosár',
            'order' => $orderBuilder,
            'setDiscountForm' => $setDiscountForm->createView(),
            'itemsInCart' => $orderBuilder->countItems(),
            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
//            'messageForm' => $messageForm->createView(),
            'progressBar' => 'pickExtraGift',
            'products' => $extras,
            'customerForm' => isset($customerForm) ? $customerForm->createView() : null,
            'messageAndCustomerForm' => isset($messageAndCustomerForm) ? $messageAndCustomerForm->createView() : null,
            'cardCategories' => $cardCategories,
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

        if (!$this->isValidOrder($orderBuilder, 1)) {
            return $this->redirectToRoute('site-checkout-step1-pickExtraGift');
        }

        $shippingMethods = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAllOrdered();

        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new \DateTime('+2 months'))->diff(new \DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[] = (new \DateTime('+'. ($i*24 + $offset).' hours'));
        }

//        dd($dates);
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

        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval);
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

        if (!$this->isValidOrder($orderBuilder, 2)) {
            return $this->redirectToRoute('site-checkout-step2-pickRecipient');
        }

        $checkoutForm = $this->createForm(CheckoutFormType::class, $orderBuilder->getCurrentOrder());

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
            $sender->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $senderForm = $this->createForm(SenderType::class, $sender);

            return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
                'title' => 'Szállítás és fizetés',
                'order' => $orderBuilder,
                'form' => $checkoutForm->createView(),
                'shippingMethods' => $shippingMethods,
                'paymentMethods' => $paymentMethods,
                'senderForm' => $senderForm->createView(),
                'progressBar' => 'pickPayment',
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
        ]);
    }

    /**
     * @Route("/rendeles/koszonjuk", name="site-checkout-step4-success")
     */
    public function checkoutThankyou()
    {
        $orderBuilder = $this->orderBuilder;
        if (!$this->isValidOrder($orderBuilder, 3)) {
            return $this->redirectToRoute('site-checkout-step3-pickPayment');
        }
        $paymentMethod = $orderBuilder->getCurrentOrder()->getPayment()->isBankTransfer() ? true : false;

        return $this->render('webshop/site/checkout_thankyou.html.twig',[
            'title' => 'Sikeres rendelés!',
            'order' => $orderBuilder,
            'progressBar' => 'thankyou',
            'paymentMethod' => $paymentMethod,
        ]);
    }


    /**
     * Checks if the Order is valid at a given step in the Checkout process.
     * Returns 'true' if it's valid.
     * This is executed when next step is loaded, either
     * directly by URL or by clicking Continue button.
     */
    public function isValidOrder(OrderBuilder $orderBuilder, int $checkoutStep)
    {
        $validOrder = true;
        if ($checkoutStep == 1) {
            if (!$orderBuilder->hasItems()) {
                $validOrder = false;
                $this->addFlash('items-missing', 'A kosarad üres.');
            }
            if (!$orderBuilder->hasCustomer()) {
                $validOrder = false;
                $this->addFlash('customer-missing', 'Nem adtad meg az adataidat vagy valamelyik mezőt nem tölötted ki.');
            }
//            if (!$orderBuilder->hasMessage()) {
//                $validOrder = false;
//                $this->addFlash('message-warning', 'Ha szeretnél üzenni a virággal, itt tudod kifejezni pár szóban, mit írjunk az üdvözlőlapra. (Nem kötelező)');
//            }
            if ($validOrder) {
                return true;
            } else {
                return false;
            }
        }

        if ($checkoutStep == 2) {
            if (!$orderBuilder->hasRecipient()) {
                $validOrder = false;
                $this->addFlash('recipient-missing', 'Nem adtál meg címzettet.');
            }
            if (!$orderBuilder->hasDeliveryDate()) {
                $validOrder = false;
                $this->addFlash('date-missing', 'Add meg a szállítás idejét! Bizonyosodj meg róla, hogy választottál szállítási idősávot is.');
            }
            if ($orderBuilder->isDeliveryDateInPast()) {
                $validOrder = false;
                $this->addFlash('date-missing', 'Nem adtál meg szállítási napot! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
            }
//            if (!$orderBuilder->hasShipping()) {
//                $validOrder = false;
//                $this->addFlash('shipping-missing', 'Válassz szállítási módot.');
//            }
            if ($validOrder) {
                return true;
            } else {
                return false;
            }
        }

        if ($checkoutStep == 3) {
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
                return true;
            } else {
                return false;
            }
        }
    }

}