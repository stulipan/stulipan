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
use App\Entity\Transaction;
use App\Form\AcceptTermsType;
use App\Form\Customer\CustomerType;
use App\Model\CartGreetingCard;

use App\Model\CheckoutPaymentMethod;
use App\Model\CheckoutShippingMethod;
use App\Entity\Order;
use App\Services\OrderBuilder;
use App\Entity\OrderLog;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Product\ProductCategory;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\ShippingMethod;
use App\Entity\PaymentMethod;

use App\Entity\User;
use App\Event\OrderEvent;
use App\Form\DeliveryDate\CartHiddenDeliveryDateFormType;
use App\Form\Checkout\PaymentMethodType;
use App\Form\GreetingCard\GreetingCardFormType;
use App\Form\RecipientType;
use App\Form\SenderType;
use App\Form\SetDiscountType;

use App\Form\Checkout\ShippingMethodType;
use App\Form\UserRegistration\UserRegistrationFormType;
use App\Services\CheckoutSettings;
use App\Services\PaymentBuilder;
use App\Services\StoreSettings;
use App\Stulipan\Cashin\CashinBundle;
use App\Stulipan\Cashin\Model\Enumerations\CashinEnvironment;
use App\Stulipan\GatewayCib\GatewayCibBundle;
use App\Stulipan\GatewayCib\Model\Enumerations\CibEnvironment;
use App\Stulipan\GatewayCib\Model\PaymentRequest;
use App\Stulipan\GatewayCib\Model\PaymentResponse;
use App\Stulipan\GatewayCib\Model\PaymentStatusRequest;
use App\Stulipan\GatewayCib\Model\PaymentStatusResponse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;


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
    private $translator;
    private $em;
    private $urlGenerator;
    private $barionClient;
    private $cibClient;
    private $des;
    private $gateway;

    private $storeSettings;
    private $checkoutSettings;

    public function __construct(OrderBuilder $orderBuilder, TranslatorInterface $translator, EntityManagerInterface $entityManager,
                                StoreSettings $storeSettings, CheckoutSettings $checkoutSettings,
                                UrlGeneratorInterface $urlGenerator, PaymentBuilder $gateway)
    {
        $this->orderBuilder = $orderBuilder;
        $this->translator = $translator;
        $this->em = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->storeSettings = $storeSettings;
        $this->checkoutSettings = $checkoutSettings;
        $this->gateway = $gateway;

//        // Barion init
//        $myPosKey = '6d53dfe8c2b04b60b33ecbedd857f6ff'; // Pikk Pakk fiok
//        $apiVersion = 2;
//        $environment = \BarionEnvironment::Test;
//
//        /** @var \BarionClient $barionClient */
//        $this->barionClient = new \BarionClient($myPosKey, $apiVersion, $environment);
        $this->barionClient = $gateway->createBarionClient();

//        // Cib init
//        $myPid = 'YSC0001'; // Maysa Car fiok
//        $environment = CibEnvironment::TEST;
//
////        $projectDir = $this->getParameter('kernel.project_dir');
////        $des = $projectDir . '/config/cib/' . 'YSC.des';
//        $des = __DIR__ . '/../../../config/cib/' . 'YSC.des';
//
//        $this->cibClient = new GatewayCibBundle($myPid, $des, $environment);
//        $this->des = $des;
        $this->cibClient = $gateway->createCibClient();

    }

    /**
     * @Route("/rendeles/ajandek", name="site-checkout-step0-pickExtraGift")
     */
    public function checkoutStep0PickExtraGift(StoreSettings $settings)
    {
        $orderBuilder = $this->orderBuilder;
        $user = $this->getUser();

        $setDiscountForm = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());

        $giftCategory = $this->em->getRepository(ProductCategory::class)->find($settings->get('general.giftCategory'));
        $extras = $giftCategory->getProducts();

        $greetingCard = new CartGreetingCard($orderBuilder->getCurrentOrder()->getMessage(), $orderBuilder->getCurrentOrder()->getMessageAuthor());
        $greetingCardForm = $this->createForm(GreetingCardFormType::class, $greetingCard);

        $cardCategories = $this->em->getRepository(GreetingCardMessageCategory::class)
            ->findAll();

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
        $user = $this->getUser();

        $validation = $this->validatePreviousStep($orderBuilder, self::STEP_CART);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $customer = $orderBuilder->getCustomer();

        if ($user) {
            /** If before login a Recipient was added to the Order, assign the current Customer to this Recipient */
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);   //// ????
                $this->em->persist($recipientInOrder);
                $this->em->flush();
            }
        }

        $recipient = null;
        if ($user && $user->getCustomer()) {
            /** If Customer exists (is logged in), get all its Recipients and Senders */
            $recipients = $user->getCustomer()->getRecipients();

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
        else {
            /** Else, simply return the Recipient/Sender saved already in the Order (This is the Guest Checkout scenario) */
            $recipients = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getRecipient()) {
                $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
            }
            if ($orderBuilder->hasRecipient()) {
                $recipient = $orderBuilder->getCurrentOrder()->getRecipient();
                $orderBuilder->setRecipient($recipient);
            }
        }
//        if (!$customer) {
//            $customer = new Customer();
//        }
//        $customerBasic = new CustomerBasic(
//            $customer && $customer->getEmail() ? $customer->getEmail() : $orderBuilder->getCurrentSession()->fetch('email'),
//            $customer && $customer->getFirstname() ? $customer->getFirstname() : $orderBuilder->getCurrentSession()->fetch('firstname'),
//            $customer && $customer->getLastname() ? $customer->getLastname() : $orderBuilder->getCurrentSession()->fetch('lastname'),
//            $customer && $customer->getPhone() ? $customer->getPhone() : $orderBuilder->getCurrentOrder()->getBillingPhone()
//        );

//        if ($recipients->isEmpty()) {
        if (!$recipient) {
            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
        }

//        dd($orderBuilder->getCustomer());

        return $this->render('webshop/cart/checkout-step1-pickDeliveryAddress.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'recipients' => $recipients,
            'recipientForm' => $this->createForm(RecipientType::class, $recipient)->createView(),
            'selectedRecipient' => null !== $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
            'progressBar' => 'pickDeliveryAddress',
            'customerForm' => $this->createForm(CustomerType::class, $customer)->createView(),
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
        $shippingMethods = $this->em->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']);

//        $customer = $this->getUser();
        $customer = $orderBuilder->getCustomer();

        /** After login, add current user as Customer (to the current Order and to the current Recipient also) */
        if ($customer) {
//            $orderBuilder->setCustomer($customer);
            /** If before login a Recipient was added to the Order, assign the current Customer to this Recipient */
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $this->em->persist($recipientInOrder);
                $this->em->flush();
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
//        $user->setEmail($orderBuilder->getCurrentSession()->fetch('email'));
//        $user->setFirstname($orderBuilder->getCurrentSession()->fetch('firstname'));
//        $user->setLastname($orderBuilder->getCurrentSession()->fetch('lastname'));
        $user->setEmail($orderBuilder->getCustomer()->getEmail());
        $user->setFirstname($orderBuilder->getCustomer()->getFirstname());
        $user->setLastname($orderBuilder->getCustomer()->getLastname());
        $registrationForm = $this->createForm(UserRegistrationFormType::class, $user);

        $shippingMethods = $this->em->getRepository(ShippingMethod::class)->findAll();
        $paymentMethods = $this->em->getRepository(PaymentMethod::class)->findAllOrdered();
        $acceptTermsForm = $this->createForm(AcceptTermsType::class, ['isAcceptedTerms' => $orderBuilder->getCurrentOrder()->isAcceptedTerms()]);

//        $customer = $this->getUser();
        $customer = $orderBuilder->getCustomer();

        /** After login, add current user as Customer (to the current Order and to the current Recipient also) */
        if ($customer) {
//            $orderBuilder->setCustomer($customer);
            /** If before login a Sender was added to the Order, asign the current Customer to this Sender */
            $senderInOrder = $orderBuilder->getCurrentOrder()->getSender();
            if ($senderInOrder) {
                $senderInOrder->setCustomer($customer);
                $this->em->persist($senderInOrder);
                $this->em->flush();
            }
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $this->em->persist($recipientInOrder);
                $this->em->flush();
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
                $sender->setFirstname($customer->getFirstname());
                $sender->setLastname($customer->getLastname());
//                $sender->setPhone($customer->getPhone());
            }
            
            $sender->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
            $senderForm = $this->createForm(SenderType::class, $sender);

            return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'shippingMethods' => $shippingMethods,
                'paymentMethods' => $paymentMethods,
                'hasShipping' => $orderBuilder->getCurrentOrder()->getShippingMethod() ? 'true' : 'false',
                'hasPayment' => $orderBuilder->getCurrentOrder()->getPaymentMethod() ? 'true' : 'false',
                'paymentMethodForm' => $this->createForm(PaymentMethodType::class, (new CheckoutPaymentMethod($orderBuilder->getCurrentOrder()->getPaymentMethod())))->createView(),
                'senders' => $senders,
                'senderForm' => $senderForm->createView(),
                'progressBar' => 'pickPayment',
                'acceptTermsForm' => $acceptTermsForm->createView(),
                'registrationForm' => $registrationForm->createView(),
            ]);
        }

        return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
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
            'acceptTermsForm' => $acceptTermsForm->createView(),
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * Ez rögzíti a rendelést (azaz törli a sessionből) és tovább küldi a 'step4Thankyou'-ra,
     * ahol a Thankyou oldal megjelenítése történik.
     * Ez csupán annyit csinál, hogy megjeleníti a Thank you oldal.
     *
     * @Route("/rendeles/leadas", name="site-checkout-place-order", methods={"POST", "GET"})
     */
    public function placeOrder(EventDispatcherInterface $eventDispatcher)
    {
        $orderBuilder = $this->orderBuilder;
        $validation = $this->validatePreviousStep($orderBuilder, self::STEP_PAYMENT_METHOD);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $status = $this->em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::ORDER_CREATED]);
        // paymentStatus must be deprecated, as this info will be gained from Transaction
        $paymentStatus = $this->em->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => PaymentStatus::STATUS_PENDING]);
        $orderBuilder->setStatus($status);
        $orderBuilder->setPaymentStatus($paymentStatus);
        $orderBuilder->setToken((Uuid::v4())->toRfc4122());

        $event = new OrderEvent($orderBuilder->getCurrentOrder(), [
            'channel' => OrderLog::CHANNEL_CHECKOUT,
        ]);
        $eventDispatcher->dispatch($event, OrderEvent::DELIVERY_DATE_UPDATED);

        $order = $orderBuilder->getCurrentOrder();

        $payment = $this->gateway->createPayment(null, $order);
        if ($payment->isCreated()) {
            return $this->redirect($payment->getPaymentPageUrl(), 302);
        } else {
            return $this->redirectToRoute($payment->getErrorRoute());
        }

        // IF not $payment (eg: COD or Bank transfer)
        return $this->redirectToRoute('site-checkout-payment-success');
    }

    /**
     * @Route("/rendeles/fizetes/callback", name="site-checkout-payment-callback", methods={"POST"})
     */
    public function processCallback(Request $request): Response
    {
        $paymentId = $request->get('paymentId');
        $paymentResponse = $this->gateway->getBarion()->GetPaymentState($paymentId);

        $transaction = $this->em->getRepository(Transaction::class)->findOneBy(['authorization' => $paymentId]);

        if (!$transaction) {
            throw $this->createNotFoundException('The order does not exist');
        }

//        $order = $transaction->getOrder();

        if ($transaction->getStatus() === Transaction::STATUS_PENDING) {
            if ($paymentResponse->RequestSuccessful) {
                switch ($paymentResponse->Status) {
                    case \PaymentStatus::Succeeded:
                        $transaction->setStatus(Transaction::STATUS_SUCCESS);
                        break;
//                case (\PaymentStatus::Started || \PaymentStatus::InProgress || \PaymentStatus::Authorized || \PaymentStatus::Reserved):
//                    $transaction->setStatus(Transaction::STATUS_PENDING);
//                    break;
                    case (\PaymentStatus::Canceled || \PaymentStatus::Failed || \PaymentStatus::Expired):
                        $transaction->setStatus(Transaction::STATUS_FAILURE);
                        break;
                }
                $transaction->setStatus($paymentResponse->Status);
                $transaction->setProcessedAt(new DateTime('now'));
                $this->em->persist($transaction);
                $this->em->flush();
            }
        }

        return new Response('content', 200);
    }

    /**
     * @Route("/rendeles/fizetes/sikeres", name="site-checkout-payment-success", methods={"POST", "GET"})
     */
    public function handlePaymentSuccess(CheckoutSettings $checkoutSettings, Request $request)
    {
        $orderBuilder = $this->orderBuilder;
        $order = $orderBuilder->getCurrentOrder();
        $testMode = $checkoutSettings->get('test-mode.test-mode');
        $paymentId = $request->get('paymentId');

//        if ($paymentId) {
//            $this->processCallback($request);
//        }

        /** When at this step, the Order has been
         * successfully placed, so it can be removed from session */
        if (!$testMode) {
            $orderBuilder->getCurrentSession()->removeOrderFromSession();
        }

        return $this->redirectToRoute('site-checkout-step4-thankyou', [
            'orderNumber' => $order->getNumber(),
            'orderToken' => $order->getToken(),
        ]);
    }

    /**
     * Rögzíti a rendelést (azaz törli a sessionből) és tovább küld a 'placeOrder'-re,
     * ahol a Thankyou oldal megjelenítése történik.
     *
     * @Route("/rendeles/koszonjuk/{orderNumber}/{orderToken}", name="site-checkout-step4-thankyou", methods={"GET"})
     */
    public function step4Thankyou(EventDispatcherInterface $eventDispatcher, int $orderNumber, string $orderToken)
    {

//        $projectDir = $this->getParameter('kernel.project_dir');
//        $des = $projectDir . '/config/cib/' . 'YSC.des';
////        dd($this->getParameter('kernel.project_dir') . '/config' . '/cib/' . 'YSC.des');
////        dd(__DIR__ . '/config');
////        $des = __DIR__ . '/../../../config/cib/' . 'YSC.des';
//        $encodedQuery = ekiEncode('pid=ABC&id=1234', $des);
//        dd($encodedQuery);

        $orderBuilder = $this->orderBuilder;

        if ($orderNumber && $orderToken) {
            /** @var Order $order */
            $order = $this->em->getRepository(Order::class)->findOneBy(['number' => $orderNumber, 'token' => $orderToken]);
        }

        if ($order) {
            $isConversionTracked = false;
            if ($order->getIsConversionTracked()) {
                $isConversionTracked = true;
            }
            $event = new OrderEvent($order, [
                'conversionTrackingStatus' => OrderStatus::CONVERSION_TRACKING_LOADED,
            ]);
            $eventDispatcher->dispatch($event, OrderEvent::SET_ORDER_AS_TRACKED);

            $isBankTransfer = $order->getPaymentMethod()->isBankTransfer() ? true : false;

            return $this->render('webshop/cart/checkout-step4-thankyou.html.twig', [
                'order' => $order,
                'isBankTransfer' => $isBankTransfer,
                'isConversionTracked' => $isConversionTracked,
            ]);
        }
        throw $this->createNotFoundException('The order does not exist');
    }

//    private function createBarionPayment(Order $order): ?\PreparePaymentResponseModel
//    {
//        $trans = new \PaymentTransactionModel();
//        $trans->POSTransactionId = $order->getNumber(); //$transaction->getId(); //"TRANS-01";
//        $trans->Payee = 'payment@hivjesnyerj.hu';  /////// WEBSHOP BARION FIOK EMAIL CIM
//        $trans->Total = $order->getSummary()->getTotalAmountToPay();
//        $trans->Currency = \Currency::HUF;
//        $trans->Comment = "Test transaction containing the product";
//
//        foreach ($order->getItems() as $i) {
//            $item = new \ItemModel();
//            $item->Name = $i->getProduct()->getName();
//            $item->Description = $i->getProduct()->getName();
//            $item->Quantity = $i->getQuantity();
//            $item->Unit = "piece";
//            $item->UnitPrice = $i->getUnitPrice();
//            $item->ItemTotal = $i->getPriceTotal();
//            $item->SKU = $i->getProduct()->getSku();
//
//            $trans->AddItem($item);
//        }
//
//        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-success', [], UrlGeneratorInterface::ABSOLUTE_URL);
//        $urlCallback = $this->urlGenerator->generate('site-checkout-payment-callback', [], UrlGeneratorInterface::ABSOLUTE_URL);
//
//        $paymentRequest = new \PreparePaymentRequestModel();
//        $paymentRequest->GuestCheckout = true;
//        $paymentRequest->PaymentType = \PaymentType::Immediate;
//        $paymentRequest->FundingSources = array(\FundingSourceType::All);
//        $paymentRequest->PaymentRequestId = 'PAYMENT-'.$order->getNumber(); //"PAYMENT-01";
//        $paymentRequest->PayerHint = $order->getCustomer()->getEmail();
//        $paymentRequest->Locale = \UILocale::HU;
//        $paymentRequest->OrderNumber = $order->getNumber();
//        $paymentRequest->Currency = \Currency::HUF;
//        $paymentRequest->RedirectUrl = $urlRedirect; // "http://webshop.example.com/afterpayment";
//        $paymentRequest->CallbackUrl = $urlCallback; // "http://webshop.example.com/processpayment";
//        $paymentRequest->AddTransaction($trans);
//
//        $paymentIntent = $this->barionClient->PreparePayment($paymentRequest);
//        if ($paymentIntent->RequestSuccessful) {
//            $transaction = new Transaction();
//            $transaction->setKind(Transaction::KIND_SALE);
//            $transaction->setAuthorization($paymentIntent->PaymentId);
//            $transaction->setGateway(PaymentMethod::BARION);
//            $transaction->setSourceName(Transaction::SOURCE_WEB);
//            $transaction->setOrder($order);
//            $transaction->setAmount($order->getSummary()->getTotalAmountToPay());
//            $transaction->setCurrency('HUF');
//
//            $transaction->setStatus(Transaction::STATUS_PENDING);
//
//            $order->addTransaction($transaction);
//            $this->em->persist($order);
//            $this->em->flush();
//            return $paymentIntent;
//        }
//        return null;
//    }

//    private function createCibPayment(Order $order): ?PaymentResponse
//    {
////        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-success', [], UrlGeneratorInterface::ABSOLUTE_URL);
//        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-cib', [], UrlGeneratorInterface::ABSOLUTE_URL);
//        $urlCallback = $this->urlGenerator->generate('site-checkout-payment-callback', [], UrlGeneratorInterface::ABSOLUTE_URL);
//
//        $paymentRequest = new PaymentRequest();
//        $paymentRequest->uid = 'CIB12345678';
//        $paymentRequest->amount = $order->getSummary()->getTotalAmountToPay();
//        $paymentRequest->urlReturn = $urlRedirect;
//
//
//        $paymentIntent = $this->cibClient->preparePayment($paymentRequest);
////        dd($paymentIntent);
//        if ($paymentIntent->requestSuccessful && $paymentIntent->responseCode === PaymentResponse::SUCCESSFUL_INITIALIZATION) {
//            $transaction = new Transaction();
//            $transaction->setKind(Transaction::KIND_SALE);
//            $transaction->setAuthorization($paymentIntent->transactionId);
//            $transaction->setGateway(PaymentMethod::CREDIT_CARD);
//            $transaction->setSourceName(Transaction::SOURCE_WEB);
//            $transaction->setOrder($order);
//            $transaction->setAmount($order->getSummary()->getTotalAmountToPay());
//            $transaction->setCurrency('HUF');
//
//            $transaction->setStatus(Transaction::STATUS_PENDING);
//
//            $order->addTransaction($transaction);
//            $this->em->persist($order);
//            $this->em->flush();
//            return $paymentIntent;
//        }
//        return null;
//    }

    /**
     * @Route("/cib", name="site-checkout-payment-cib", methods={"POST", "GET"})
     */
    public function handleRedirectCib2Merchant(CheckoutSettings $checkoutSettings, Request $request)
    {
        $orderBuilder = $this->orderBuilder;
        $transactionId = $orderBuilder->getCurrentOrder()->getTransactions()->last()->getAuthorization();

        $query = $request->getQueryString();
        $response = ekiDecode($query, $this->des);

        $paymentResponse = new PaymentResponse();
        if (!empty($response)) {
            parse_str($response, $json);
            $paymentResponse->fromJson($json);

            if ($paymentResponse->requestSuccessful && $paymentResponse->transactionId == (string) $transactionId) {
                // build a StatusRequest to query for the payment's status
                $statusRequest = new PaymentStatusRequest(
                    '33',
                    $paymentResponse->pid,
                    $paymentResponse->transactionId,
                    $orderBuilder->getCurrentOrder()->getSummary()->getTotalAmountToPay()
                );
                $statusResponse = $this->cibClient->preparePaymentStatus($statusRequest);
//                dd($statusResponse);
                if ($statusResponse->responseCode === PaymentStatusResponse::SUCCESSFUL_AUTHORIZATION) {
                    return $this->redirectToRoute('site-checkout-payment-success');
                } else {
//                    dd('error');
                    $this->addFlash('payment-failed', 'Payment failed!');
                    return $this->redirectToRoute('site-checkout-step3-pickPayment');
                }
            }
        }
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
//                $this->addFlash('items-missing', 'A kosarad üres.');
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
                $this->addFlash('recipient-missing', $this->translator->trans('checkout.recipient.missing-recipient'));
            }
            if (!$orderBuilder->hasCustomer()) {
                $validOrder = false;
                $this->addFlash('customer-missing', $this->translator->trans('checkout.customer.missing-customer'));
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
                $this->addFlash('shipping-missing', $this->translator->trans('checkout.shipping.shipping-method-missing'));
            }

            if ($this->storeSettings->get('general.flower-shop-mode')) {
                if (!$orderBuilder->hasDeliveryDate()) {
                    $validOrder = false;
                    $this->addFlash('date-missing', $this->translator->trans('checkout.delivery-date.delivery-date-missing'));
                }
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

            $specialDate = $this->em->getRepository(DeliverySpecialDate::class)
                ->findOneBy(['specialDate' => $date]);

            if (!$specialDate) {
                $dateType = $this->em->getRepository(DeliveryDateType::class)
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