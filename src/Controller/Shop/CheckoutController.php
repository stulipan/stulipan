<?php

namespace App\Controller\Shop;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\GreetingCardMessageCategory;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Geo\GeoCountry;
use App\Entity\Model\CustomerBasic;
use App\Entity\Model\GeneratedDates;
use App\Entity\Model\DeliveryDateWithIntervals;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\PaymentFundingDetail;
use App\Entity\PaymentTransaction;
use App\Entity\Product\Product;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductStatus;
use App\Entity\StoreEmailTemplate;
use App\Event\StoreEvent;
use App\Form\Checkout\AcceptTermsType;
use App\Form\Checkout\SameAsRecipientType;
use App\Form\Customer\CustomerType;
use App\Form\CustomerBasic\CustomerBasicType;
use App\Model\CartGreetingCard;

use App\Model\CheckoutPaymentMethod;
use App\Model\CheckoutShippingMethod;
use App\Entity\Order;
use App\Services\CartBuilder;
use App\Services\CheckoutBuilder;
use App\Services\EmailSender;
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
use App\Services\StoreSessionStorage;
use App\Services\StoreSettings;
use App\Stulipan\GatewayCib\GatewayCibBundle;
use App\Stulipan\GatewayCib\Model\Enumerations\CibEnvironment;
use App\Stulipan\GatewayCib\Model\PaymentRequest;
use App\Stulipan\GatewayCib\Model\PaymentResponse;
use App\Stulipan\GatewayCib\Model\PaymentStatusRequest;
use App\Stulipan\GatewayCib\Model\PaymentStatusResponse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use phpDocumentor\Reflection\Types\This;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Stulipan\Traducible\StulipanTraducibleBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    private $orderBuilder;
    private $cartBuilder;
    private $checkoutBuilder;
    private $translator;
    private $em;
    private $urlGenerator;
    private $eventDispatcher;
    private $cibClient;
    private $des;
    private $paymentBuilder;

    private $storeSettings;
    private $checkoutSettings;
    private $token;
    private $traducible;

    public function __construct(OrderBuilder $orderBuilder, CartBuilder $cartBuilder, CheckoutBuilder $checkoutBuilder,
                                TranslatorInterface $translator, EntityManagerInterface $entityManager,
                                StoreSettings $storeSettings, CheckoutSettings $checkoutSettings,
                                UrlGeneratorInterface $urlGenerator, EventDispatcherInterface $eventDispatcher, PaymentBuilder $paymentBuilder,
                                StulipanTraducibleBundle $traducible)
    {
        $this->orderBuilder = $orderBuilder;
        $this->cartBuilder = $cartBuilder;
        $this->checkoutBuilder = $checkoutBuilder;
        $this->translator = $translator;
        $this->em = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->eventDispatcher = $eventDispatcher;
        $this->storeSettings = $storeSettings;
        $this->checkoutSettings = $checkoutSettings;
        $this->paymentBuilder = $paymentBuilder;

        $this->cibClient = $paymentBuilder->createCibClient();

        $this->traducible = $traducible;
    }

    /**
     * When pressing the 'Go to Checkout' on the Cart page this URL is being launched.
     *
     *
     * @Route("/order/initializeCheckout", name="site-checkout-initializeCheckout", methods={"POST", "GET"})
     */
    public function initializeCheckout()
    {
        $validation = $this->validateCart();
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $checkoutBuilder->initializeCheckout();
        return $this->redirectToRoute('site-checkout-step1-pickDeliveryAddress', [
            'checkoutToken' => $checkoutBuilder->getCurrent()->getToken(),
        ]);
    }

    /**
     * @Route("/order/delivery-address/{checkoutToken}", name="site-checkout-step1-pickDeliveryAddress")
     */
    public function step1PickDeliveryAddress(SessionInterface $session, string $checkoutToken = null)
    {
//        $cmf = $this->em->getMetadataFactory();
//        $class = $cmf->getMetadataFor(Cart::class);
////        dd($class->fieldMappings);
//        foreach ($class->fieldMappings as $fieldMapping) {
//            dump($fieldMapping);
//        }
//        dd('sop');


//        $badges = $this->em->getRepository(ProductBadge::class)->findAll();
//        /** @var ProductBadge $badge */
//        $badge = $badges[0];
//        $badge->translate('en')->setName('Bestseller');
//        $badge->translate('hu')->setName('Hónap sztárja');
//
//        $badges[1]->translate('en')->setName('Sale');
//        $badges[1]->translate('hu')->setName('Akció');
//        $this->em->persist($badges[1]);
//
//        $badges[2]->translate('en')->setName('Trending');
//        $badges[2]->translate('hu')->setName('Felkapott');
//        $this->em->persist($badges[2]);
//
//        $badges[3]->translate('en')->setName('Top product');
//        $badges[3]->translate('hu')->setName('Hónap sztárja');
//        $this->em->persist($badges[3]);
//
////        dd($badge);
////        dd($badge->getNewTranslations()->getValues());
////        dd($badge->getName());
////        dd($badge->translate()->getName());
//
////        $this->em->persist($badge);
////
//        // In order to persist new translations, call mergeNewTranslations method, before flush
//        $badge->mergeNewTranslations();
//        $badges[1]->mergeNewTranslations();
//        $badges[2]->mergeNewTranslations();
//        $badges[3]->mergeNewTranslations();
//        $this->em->flush();
//

        $validation = $this->validateCart();
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

//        dd((Uuid::v4())->toRfc4122());

        $checkoutBuilder = $this->checkoutBuilder;

        $user = $this->getUser();

        $customer = $checkoutBuilder->getCustomer();
        $customerBasic = new CustomerBasic();
        if ($customer) {
            $customerBasic->setEmail($customer->getEmail());
            $customerBasic->setAcceptsMarketing($customer->isAcceptsMarketing());
        }

        $recipient = $checkoutBuilder->getCurrent()->getRecipient();
        if (!$recipient) {
            $recipient = new Recipient();
            if ($user) {
                $recipient->setUser($user);
            }
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
        }

        if ($user) {
            $recipients = $user->getRecipients();
        } else {
            $recipients = new ArrayCollection();
        }

        return $this->render('webshop/cart/checkout-step1-pickDeliveryAddress.html.twig', [
            'customerForm' => $this->createForm(CustomerBasicType::class, $customerBasic)->createView(),
            'recipientForm' => $this->createForm(RecipientType::class, $recipient)->createView(),
            'selectedRecipient' => null !== $checkoutBuilder->getCurrent()->getRecipient() ? $checkoutBuilder->getCurrent()->getRecipient()->getId() : null,
            'recipients' => $recipients,
            'checkout' => $this->checkoutBuilder->getCurrent()->getId() !== null ? $this->checkoutBuilder->getCurrent() : $this->cartBuilder->getCurrent(),
            'progressBar' => 'pickDeliveryAddress',
            'user' => $user,
        ]);
    }

    /**
     * @Route("/order/shipping", name="site-checkout-step2-pickShipping")
     */
    public function step2PickShipping()
    {
        $validation = $this->validatePreviousStep(self::STEP_DELIVERY_ADDRESS);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $shippingMethods = $this->em->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']);
        $selectedDate = null === $checkoutBuilder->getCurrent()->getDeliveryDate() ? null : $checkoutBuilder->getCurrent()->getDeliveryDate();
        $selectedInterval = null === $checkoutBuilder->getCurrent()->getDeliveryInterval() ? null : $checkoutBuilder->getCurrent()->getDeliveryInterval();

//        dd($checkoutBuilder->getCurrent()->getCustomer());
        return $this->render('webshop/cart/checkout-step2-pickShipping.html.twig', [
            'checkout' => $checkoutBuilder->getCurrent(),
            'generatedDates' => $this->generateDates(),
            'hiddenDateForm' => $this->createHiddenDateForm()->createView(),
            'selectedDate' => $selectedDate,
            'selectedInterval' => $selectedInterval,
            'shippingMethods' => $shippingMethods,
            'shippingMethodForm' => $this->createForm(ShippingMethodType::class, (new CheckoutShippingMethod($checkoutBuilder->getCurrent()->getShippingMethod())))->createView(),
            'progressBar' => 'pickShipping',
        ]);
    }

    /**
     * @Route("/order/payment", name="site-checkout-step3-pickPayment")
     */
    public function step3PickPayment()
    {
        $validation = $this->validatePreviousStep(self::STEP_SHIPPING_METHOD);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $user = $this->getUser();
        $checkoutBuilder = $this->checkoutBuilder;

        $newUser = new User();
        $newUser->setEmail($checkoutBuilder->getCustomer()->getEmail());
//        $newUser->setFirstname($checkoutBuilder->getCustomer()->getFirstname());
//        $newUser->setLastname($checkoutBuilder->getCustomer()->getLastname());
//        $newUser->setPhone($checkoutBuilder->getCustomer()->getPhone());
        $registrationForm = $this->createForm(UserRegistrationFormType::class, $newUser);

        $paymentMethods = $this->em->getRepository(PaymentMethod::class)->findAllOrdered();
        $acceptTermsForm = $this->createForm(AcceptTermsType::class, ['isAcceptedTerms' => $checkoutBuilder->getCurrent()->isAcceptedTerms()]);
        $sameAsRecipient = $checkoutBuilder->getCurrent()->isSameAsShipping();
        $sameAsRecipientForm = $this->createForm(SameAsRecipientType::class, ['sameAsRecipient' => $sameAsRecipient]);
        $isNewSender = ($sameAsRecipient === null || $sameAsRecipient === true) ? false : true;

//////////////////// Ehhez kapcsolodik a CheckoutBuilder::setSender()-ben levo kikomentelt resz !! //////////////
//        $sender = null;
//        // Any Sender in db is relevant ONLY when the same_as_recipient = false
//        if ($sameAsRecipient === false) {
//            $sender = $checkoutBuilder->getCurrent()->getSender();
//        }
//////////////////// Ehhez kapcsolodik a CheckoutBuilder::setSender()-ben levo kikomentelt resz !! //////////////

        $sender = $checkoutBuilder->getCurrent()->getSender();
        if (!$sender) {
            $sender = new Sender();
            if ($user) {
                $sender->setUser($user);
            }
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
        }
        $senderForm = $this->createForm(SenderType::class, $sender);

        if ($user) {
            $senders = $user->getSenders();
        } else {
            $senders = new ArrayCollection();
        }

        return $this->render('webshop/cart/checkout-step3-pickPayment.html.twig', [
            'checkout' => $checkoutBuilder->getCurrent(),
            'paymentMethods' => $paymentMethods,
            'paymentMethodForm' => $this->createForm(PaymentMethodType::class, (new CheckoutPaymentMethod($checkoutBuilder->getCurrent()->getPaymentMethod())))->createView(),
            'senders' => $senders,
            'senderForm' => $senderForm->createView(),
            'sameAsRecipientForm' => $sameAsRecipientForm->createView(),
            'isNewSender' => $isNewSender,
            'selectedSender' => null !== $checkoutBuilder->getCurrent()->getSender() ? $checkoutBuilder->getCurrent()->getSender()->getId() : null,
            'progressBar' => 'pickPayment',
            'acceptTermsForm' => $acceptTermsForm->createView(),
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * Ez küldi tovább a fizetésre. Két dolog történik itt:
     *          1. We create the Order at this point, and
     *          2. we submit the Order to payment (we also redirect the user to the 3rd party payment gateway).
     *
     *
     * @Route("/order/submit", name="site-checkout-place-order", methods={"POST", "GET"})
     */
    public function submitOrderForPayment(CheckoutSettings $checkoutSettings)
    {
        $validation = $this->validatePreviousStep(self::STEP_PAYMENT_METHOD);
        if (!$validation['isValid']) {
            return $this->redirectToRoute($validation['route']);
        }

        $isTestMode = $checkoutSettings->get('testing.test-mode');

        $orderBuilder = $this->orderBuilder;
        $orderBuilder->initializeOrder();   // it also dispatches StoreEvent::ORDER_CREATE
        // TODO: the event should be dispatched here
        $order = $orderBuilder->getCurrentOrder();

        $payment = $this->paymentBuilder->createPaymentModel(null, $order, $isTestMode);
        if ($payment->isCreated()) {
            $paymentStatus = $this->paymentBuilder->computePaymentStatus($order->getTransaction());
            $orderBuilder->setPaymentStatus($paymentStatus);
            return $this->redirect($payment->getPaymentPageUrl(), 302);    // redirect the user to the 3rd party Payment Gateway.
        }

        $this->addFlash('danger', $payment->getError()->getMessage());
        $paymentStatus = $this->paymentBuilder->computePaymentStatus($order->getTransaction());
        $orderBuilder->setPaymentStatus($paymentStatus);  // Dispatches the OrderEvent::PAYMENT_UPDATED in OrderBuilder
        return $this->redirectToRoute($payment->getErrorRoute());
    }


    /**
     * @Route("/payment/callback/barion", name="site-payment-callback-barion", methods={"POST"}) //, "GET"
     */
    public function callbackBarion(Request $request): Response
    {
        $paymentId = $request->get('paymentId');
        $paymentResponse = $this->paymentBuilder->getBarion()->GetPaymentState($paymentId);
        $transaction = $this->em->getRepository(PaymentTransaction::class)->findOneBy(['authorization' => $paymentId]);

        if (!$transaction) {
//            throw $this->createNotFoundException('The order does not exist');
            return new Response('403', 403);
        }

        if ($transaction->getStatus() === PaymentTransaction::STATUS_PENDING) {
            if ($paymentResponse->RequestSuccessful) {
                if ($paymentResponse->Status === \PaymentStatus::Succeeded) {
                    $transaction->setStatus(PaymentTransaction::STATUS_SUCCESS);
                    $transaction->setProcessedAt(new DateTime('now'));
                }
                if ($paymentResponse->Status === \PaymentStatus::Failed || $paymentResponse->Status === \PaymentStatus::Canceled || $paymentResponse->Status === \PaymentStatus::Expired) {
                    $transaction->setStatus(PaymentTransaction::STATUS_FAILURE);
                    $transaction->setErrorCode(PaymentTransaction::ERROR_PROCESSING_ERROR);
                }

                if ($paymentResponse->FundingInformation) {
                    $fundingDetail = new PaymentFundingDetail();
                    $fundingDetail->setCreditCardNumber($paymentResponse->FundingInformation->BankCard->MaskedPan);
                    $fundingDetail->setCreditCardCompany($paymentResponse->FundingInformation->BankCard->BankCardType);
                    $fundingDetail->setExpiryYear($paymentResponse->FundingInformation->BankCard->ValidThruYear);
                    $fundingDetail->setExpiryMonth($paymentResponse->FundingInformation->BankCard->ValidThruMonth);
                    $this->em->persist($fundingDetail);
                    $transaction->setFundingDetail($fundingDetail);
                }

                /** @var Order $order */
                $order = $transaction->getOrder();
                $paymentStatus = $this->paymentBuilder->computePaymentStatus($transaction);
                $order->setPaymentStatus($paymentStatus);

                $this->em->persist($order);
                $this->em->persist($transaction);
                $this->em->flush();

                $event = new StoreEvent($order, [
                    'channel' => OrderLog::CHANNEL_CHECKOUT,
                    'newPaymentStatus' => $paymentStatus->getShortcode(),
                ]);
                $this->eventDispatcher->dispatch($event, StoreEvent::ORDER_UPDATE);
            }
        }
        return new Response('ok', 200);
    }

    /**
     * Sikeres fizetés után ide küldi a vásárlót.
     *      -- Manual payment esetén egyből ide esik be
     *      -- Külsős fizetés oldalak esetén, ide irányítanak vissza. Itt ellenőrizzük, hogy sikeres volt-e a fizetés.
     *
     * Töröljuk a rendelést session-ből, majd tovább küldjük a Thank you oldalra.
     *
     * @Route("/payment/success", name="site-payment-success", methods={"POST", "GET"})
     */
    public function handlePaymentSuccess(CheckoutSettings $checkoutSettings, Request $request, EmailSender $emailSender)
    {
        $orderBuilder = $this->orderBuilder;
        $order = $orderBuilder->getCurrentOrder();
        $orderId = $order->getId();
        $testMode = $checkoutSettings->get('testing.test-mode');

        // If no Order exists in session (orderId is null)
        if ($orderId === null) {
            $this->addFlash('danger', $this->translator->trans('checkout.payment.payment-session-expired'));
            return $this->redirectToRoute('site-checkout-step3-pickPayment');
        }

        // TODO: itt érdemes lenne lecsekkolni (mint a callback-ben), hogy sikeres volt-e a fizetés, és milyen választ add itt ebben a pillanatban a Barion gateway
        /** @var PaymentTransaction $transaction */
        $transaction = $order->getTransactions()->last();
        $gateway = $transaction->getGateway();
        $transactionStatus = $transaction->getStatus();

        if ($gateway !== PaymentBuilder::MANUAL_BANK && $gateway !== PaymentBuilder::MANUAL_COD) {
            if ($transactionStatus !== PaymentTransaction::STATUS_SUCCESS) {
                $this->addFlash('danger', sprintf($this->translator->trans('checkout.payment.payment-failed').' %s', $transaction->getErrorCode()));
                return $this->redirectToRoute('site-checkout-step3-pickPayment');
            }
        }

        // If Order isn't yet created
        if ($order->getStatus() === null) {
            /** @var OrderStatus $status */
            $status = $this->em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::ORDER_CREATED]);

            // This should be deprecated
            $orderBuilder->setStatus($status);   // no longer dispatches OrderEvent::ORDER_UPDATED with $status

            $orderBuilder->setPostedAt();
            $orderBuilder->setToken((Uuid::v4())->toRfc4122());

            // Nem kuldok event-et, mivel ez nem Payment, se nem Fulfillment
//            if ($this->storeSettings->get('general.flower-shop-mode')) {
//                $event = new OrderEvent($orderBuilder->getCurrentOrder(), [
//                    'channel' => OrderLog::CHANNEL_CHECKOUT,
//                ]);
//                $this->eventDispatcher->dispatch($event, OrderEvent::DELIVERY_DATE_UPDATED);
//            }

            $paymentStatus = $this->paymentBuilder->computePaymentStatus($order->getTransaction());
            $event = new StoreEvent($order, [
                'channel' => OrderLog::CHANNEL_CHECKOUT,
                'newPaymentStatus' => $paymentStatus->getShortcode(),
            ]);
            $this->eventDispatcher->dispatch($event, StoreEvent::ORDER_UPDATE);
        }

        $emailSender->sendEmail($order, StoreEmailTemplate::ORDER_CONFIRMATION);
        $emailSender->sendEmail($order, StoreEmailTemplate::ADMIN_NEW_ORDER_NOTIFICATION, true);

        $eventEmail = new StoreEvent($order, [
            'channel' => OrderLog::CHANNEL_CHECKOUT,
        ]);
        $this->eventDispatcher->dispatch($eventEmail, StoreEvent::EMAIL_SEND_ORDER_CONFIRMATION);

        // When at this step, the Order has been successfully placed, so it can be removed from session
        if (!$testMode) {
            $orderBuilder->getCurrentSession()->removeOrderFromSession();
        }

        return $this->redirectToRoute('site-checkout-step4-thankyou', [
            'orderNumber' => $order->getNumber(),
            'orderToken' => $order->getToken(),
        ]);
    }

    /**
     * Megjeleníti a Thank you oldalt és ugyancsak itt, kezeljük, hogy csak egyszer legyen mérve
     * a Thank you oldal újboli betöltéskor.
     *
     * @Route("/order/thank-you/{orderNumber}/{orderToken}", name="site-checkout-step4-thankyou", methods={"GET"})
     */
    public function step4Thankyou(int $orderNumber, string $orderToken)
    {
        if ($orderNumber && $orderToken) {
            /** @var Order $order */
            $order = $this->em->getRepository(Order::class)->findOneBy(['number' => $orderNumber, 'token' => $orderToken]);
        }

        if ($order) {
            $isConversionTracked = false;
            if ($order->getIsConversionTracked()) {
                $isConversionTracked = true;
            }
            $eventConversion = new StoreEvent($order);
            $this->eventDispatcher->dispatch($eventConversion, StoreEvent::ORDER_TRACK_CONVERSION);

            $isBankTransfer = $order->getPaymentMethod()->isBankTransfer() ? true : false;
            return $this->render('webshop/cart/checkout-step4-thankyou.html.twig', [
                'order' => $order,
                'isBankTransfer' => $isBankTransfer,
                'isConversionTracked' => $isConversionTracked,
            ]);
        }
        throw $this->createNotFoundException('The order does not exist');
    }

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
                    $orderBuilder->getCurrentOrder()->getTotalAmountToPay()
                );
                $statusResponse = $this->cibClient->preparePaymentStatus($statusRequest);
//                dd($statusResponse);
                if ($statusResponse->responseCode === PaymentStatusResponse::SUCCESSFUL_AUTHORIZATION) {
                    return $this->redirectToRoute('site-payment-success');
                } else {
//                    dd('error');
                    $this->addFlash('payment-failed', 'Payment failed!');
                    return $this->redirectToRoute('site-checkout-step3-pickPayment');
                }
            }
        }
    }

    public function validateCart(): array
    {
        $isValid = true;
        $cartBuilder = $this->cartBuilder;
        if (!$cartBuilder->getCurrent()->hasItems()) {
            $isValid = false;
        } else {
            // Remove unavailable products from cart!
            foreach ($cartBuilder->getCurrent()->getItems() as $item) {
                if (!$item->getProduct()->isPubliclyAvailable()) {
                    $cartBuilder->removeItem($item);
                }
            }
        }

        if ($isValid) {
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
    public function validatePreviousStep(string $step): array
    {
        $isValid = true;

        if ($step === self::STEP_DELIVERY_ADDRESS) {
            $checkoutBuilder = $this->checkoutBuilder;
            // If step1 is invalid, then no need to check step2. Return route to step1
            $validation = $this->validateCart();
            if ($validation['isValid'] == false) {
                return [
                    'isValid' => false,
                    'route' => $validation['route']
                ];
            }
            // Continue normally and check step2
            if (!$checkoutBuilder->hasRecipient()) {
                $isValid = false;
                $this->addFlash('recipient-missing', $this->translator->trans('checkout.recipient.missing-recipient'));
            }
            if (!$checkoutBuilder->hasCustomer()) {
                $isValid = false;
                $this->addFlash('customer-missing', $this->translator->trans('checkout.customer.missing-customer'));
            }

            if ($isValid) {
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
            $checkoutBuilder = $this->checkoutBuilder;
            // If previous step is invalid, then no need to check this step. Simply return route to previous step
            $validation = $this->validatePreviousStep(self::STEP_DELIVERY_ADDRESS);
            if (!$validation['isValid']) {
                return [
                    'isValid' => false,
                    'route' => $validation['route']
                ];
            }
            // Continue normally and check this step
            if (!$checkoutBuilder->hasShippingMethod()) {
                $isValid = false;
                $this->addFlash('shipping-missing', $this->translator->trans('checkout.shipping.shipping-method-missing'));
            }

            if ($this->storeSettings->get('general.flower-shop-mode')) {
                if (!$checkoutBuilder->hasDeliveryDate()) {
                    $isValid = false;
                    $this->addFlash('date-missing', $this->translator->trans('checkout.delivery-date.delivery-date-missing'));
                }
            }
//            if ($orderBuilder->isDeliveryDateInPast()) {
//                $isValid = false;
//                $this->addFlash('date-missing', 'Nem adtál meg szállítási napot! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
//            }
            if ($isValid) {
                return ['isValid' => true, 'route' => null];
            } else {
                return ['isValid' => false, 'route' => 'site-checkout-step2-pickShipping'];
            }

        }

        if ($step === self::STEP_PAYMENT_METHOD) {
            $checkoutBuilder = $this->checkoutBuilder;
            // If previous step is invalid, then no need to check this step. Simply return route to previous step
            $validation = $this->validatePreviousStep(self::STEP_SHIPPING_METHOD);
            if (!$validation['isValid']) {
                return [
                    'isValid' => false,
                    'route' => $validation['route']
                ];
            }
            // Continue normally and check step3
            if (!$checkoutBuilder->hasBillingAddress()) {
                $isValid = false;
                $this->addFlash('sender-missing', 'Adj meg egy számlázási címet.');
            }
            if (!$checkoutBuilder->hasPaymentMethod()) {
                $isValid = false;
                $this->addFlash('payment-missing', 'Válassz fizetési módot.');
            }

            if ($isValid) {
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
        $selectedIntervalFee = null === $order->getShippingFee() ? null : $order->getShippingFee();

        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval, $selectedIntervalFee);
        return $this->createForm(CartHiddenDeliveryDateFormType::class,$hiddenDates);
    }

}