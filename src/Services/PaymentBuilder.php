<?php

namespace App\Services;


use App\Entity\Order;
use App\Entity\PaymentMethod;
use App\Entity\PaymentTransaction;
use App\Stulipan\Cashin\Model\Enumerations\CashinEnvironment;
use App\Stulipan\Cashin\Model\CashinErrorModel;
use App\Stulipan\Cashin\Model\CashinPaymentModel;
use App\Stulipan\GatewayCib\GatewayCibBundle;
use App\Stulipan\GatewayCib\Model\Enumerations\CibEnvironment;
use App\Stulipan\GatewayCib\Model\PaymentRequest;
use App\Stulipan\GatewayCib\Model\PaymentResponse;
use BarionClient;
use BarionEnvironment;
use Currency;
use Doctrine\ORM\EntityManagerInterface;
use FundingSourceType;
use ItemModel;
use PaymentTransactionModel;
use PaymentType;
use PreparePaymentRequestModel;
use PreparePaymentResponseModel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use UILocale;

class PaymentBuilder
{
    public const GATEWAY_BARION = 'barion';
    public const GATEWAY_CIB = 'cib';
    public const MANUAL_BANK = 'bank';
    public const MANUAL_COD = 'cod';

//    private const BARION_POS_KEY = '6566bea7445a407bb6bdcd7b2e46d593';  // Balla fiok: Tulipanfutar.hu
//    private const BARION_PAYEE_EMAIL = 'kalmucus@gmail.com';
//    private const BARION_ENVIRONMENT_PROD = true;

//    private const BARION_POS_KEY = '797efeec59e54b0c8a0f7b991cf84d9a';  // Balla TEST fiok: Tulipanfutar.hu
//    private const BARION_PAYEE_EMAIL = 'kalmucus@gmail.com';
//    private const BARION_ENVIRONMENT_PROD = false;

    private const BARION_POS_KEY = '584c6072ec374c029b2ca8e184a21a44';  // Balla fiok: Rafina.hu
    private const BARION_PAYEE_EMAIL = 'kalmucus@gmail.com';
    private const BARION_ENVIRONMENT_PROD = true;

    private const BARION_COMMENT = 'Online termék vásárlás kártyával';
    private const BARION_UNIT = 'db';

    private const RETURN_URL_UPON_SUCCESS = 'site-payment-success';
    private const RETURN_URL_CALLBACK = 'site-payment-callback-barion';
    private const RETURN_URL_UPON_PAYMENT_ERROR = 'site-checkout-step3-pickPayment';

    private $urlGenerator;
    private $em;
    private $appKernel;
    private $translator;

    private $barion;
    private $cib;

    private $shippingFeeName;
    private $shippingFeeSku;

    function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager,
                         KernelInterface $appKernel, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->em = $entityManager;
        $this->appKernel = $appKernel;
        $this->translator = $translator;

        $this->barion = $this->createBarionClient();
        $this->cib = $this->createCibClient();

        $this->shippingFeeName = $this->translator->trans('cart.shipping-fee');
        $this->shippingFeeSku = 'shipping-fee';
    }

    public function createBarionClient()
    {
        $env = BarionEnvironment::Test;
        if (defined('self::BARION_ENVIRONMENT_PROD')) {
            if (self::BARION_ENVIRONMENT_PROD) {
                $env = BarionEnvironment::Prod;
            }
        }

        /** @var BarionClient $barionClient */
        $barionClient = new BarionClient(
            self::BARION_POS_KEY,
            2,
            $env
        );
        return $barionClient;
    }

    /**
     * @return BarionClient
     */
    public function getBarion(): BarionClient
    {
        return $this->barion;
    }

    public function createCibClient()
    {
        // Cib init
        $myPid = 'YSC0001'; // Maysa Car fiok
        $environment = CibEnvironment::TEST;

        $des = $this->appKernel->getProjectDir() . '/config/cib/' . 'YSC.des';
        $cibClient = new GatewayCibBundle($myPid, $des, $environment);
        return $cibClient;
    }

    /**
     * @return GatewayCibBundle
     */
    public function getCib(): GatewayCibBundle
    {
        return $this->cib;
    }

    public function createPaymentModel(string $gateway = null, Order $order)
    {
        $payment = new CashinPaymentModel();
        $paymentMethodShortcode = $order->getPaymentMethod()->getShortcode();

        $transaction = $order->getTransaction();

        if ($transaction === null) {
            $transaction = new PaymentTransaction();
            $transaction->setSourceName(PaymentTransaction::SOURCE_WEB);
            $transaction->setOrder($order);
            $transaction->setAmount($order->getSummary()->getTotalAmountToPay());
            $transaction->setCurrency('HUF');
            $transaction->setStatus(PaymentTransaction::STATUS_PENDING);

            $order->addTransaction($transaction);
        }

        if ($paymentMethodShortcode === self::MANUAL_COD || $paymentMethodShortcode === self::MANUAL_BANK) {
            $transaction->setKind(PaymentTransaction::KIND_SALE);
            $transaction->setGateway($paymentMethodShortcode);
            $payment->setPayment($paymentMethodShortcode);  // Ennek nem sok ertelme van!!
            // When manual payments, there's no outbound URL to the payment page.
            // Instead, we set this URL towards the success page.
            $url = $this->urlGenerator->generate(self::RETURN_URL_UPON_SUCCESS, [], UrlGeneratorInterface::ABSOLUTE_URL);
            $payment->setPaymentPageUrl($url);
        }

        if ($paymentMethodShortcode === self::GATEWAY_BARION) {
            $barionPayment = $this->createBarionPayment($order); // Create Barion payment

            $transaction->setKind(PaymentTransaction::KIND_SALE);
            $transaction->setGateway(PaymentBuilder::GATEWAY_BARION);
            $transaction->setAuthorization($barionPayment->PaymentId);

            if (count($barionPayment->Errors)>0) {
                $error = new CashinErrorModel(null, null);

//                dd($barionPayment->Errors);

                switch ($barionPayment->Errors[0]->ErrorCode) {
                    case 'ShopIsClosed':
                        $error->setCode(CashinErrorModel::ERR_PROCESSING_ERROR); // $error->setErrorMessage($this->translator->get(''));
                        $error->setMessage($this->translator->trans('checkout.payment.payment-processing-error'));
                        break;
                    default:
                        $error->setCode(CashinErrorModel::ERR_PROCESSING_ERROR);
                        $error->setMessage($this->translator->trans('checkout.payment.payment-processing-error'));
                        break;
                }
                $transaction->setStatus(PaymentTransaction::STATUS_ERROR);
                $transaction->setErrorCode($error->getCode());
                $transaction->setMessage($error->getMessage());

                $transaction->setGatewayErrorCode($barionPayment->Errors[0]->ErrorCode);
                $transaction->setGatewayErrorTitle($barionPayment->Errors[0]->Title);
                $transaction->setGatewayErrorMessage($barionPayment->Errors[0]->Description);

                $payment->setError($error);
                $payment->setErrorRoute(self::RETURN_URL_UPON_PAYMENT_ERROR);
            }

            $payment->setPayment($barionPayment);
            $payment->setPaymentPageUrl($barionPayment->PaymentRedirectUrl);
        }

        if ($paymentMethodShortcode === self::GATEWAY_CIB) {
            $cibPayment = $this->createCibPayment($order);

            $transaction->setKind(PaymentTransaction::KIND_SALE);
            $transaction->setGateway(PaymentBuilder::GATEWAY_CIB);
//            $transaction->setAuthorization($barionPayment->PaymentId);

            if ($cibPayment === null) {
                // TODOx valami hiba tortent a createBarionPayment kozben, kiirni hizauzenetet
                $payment->setErrorRoute(self::RETURN_URL_UPON_PAYMENT_ERROR);
            }
            $payment->setPayment($cibPayment);
            $payment->setPaymentPageUrl($cibPayment->paymentRedirectUrl);
        }

        $this->em->persist($order);
        $this->em->flush();
        return $payment;
    }

    /**
     * @param Order $order
     * @return PreparePaymentResponseModel|mixed
     */
    public function createBarionPayment(Order $order)
    {
        $trans = new PaymentTransactionModel();
        $trans->POSTransactionId = $order->getNumber(); //$transaction->getId(); //"TRANS-01";
        $trans->Payee = self::BARION_PAYEE_EMAIL;
        $trans->Total = $order->getSummary()->getTotalAmountToPay();
        $trans->Currency = Currency::HUF;
        $trans->Comment = self::BARION_COMMENT;

        // Add order items
        foreach ($order->getItems() as $i) {
            $item = new ItemModel();
            $item->Name = $i->getProduct()->getName();
            $item->Description = $i->getProduct()->getName();
            $item->Quantity = $i->getQuantity();
            $item->Unit = self::BARION_UNIT;
            $item->UnitPrice = $i->getUnitPrice();
            $item->ItemTotal = $i->getPriceTotal();
            $item->SKU = $i->getProduct()->getSku();

            $trans->AddItem($item);
        }

        // Add shipping fee
        $item = new ItemModel();
        $item->Name = $this->shippingFeeName;
        $item->Description = $this->shippingFeeName;
        $item->Quantity = 1;
        $item->Unit = self::BARION_UNIT;
        $item->UnitPrice = $order->getShippingPriceToPay();
        $item->ItemTotal = $order->getShippingPriceToPay();
        $item->SKU = $this->shippingFeeSku;

        $trans->AddItem($item);

        $urlRedirect = $this->urlGenerator->generate(self::RETURN_URL_UPON_SUCCESS, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlCallback = $this->urlGenerator->generate(self::RETURN_URL_CALLBACK, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $paymentRequest = new PreparePaymentRequestModel();
        $paymentRequest->GuestCheckout = true;
        $paymentRequest->PaymentType = PaymentType::Immediate;
        $paymentRequest->FundingSources = array(FundingSourceType::All);
        $paymentRequest->PaymentRequestId = 'PAYMENT-'.$order->getNumber(); //"PAYMENT-01";
        $paymentRequest->PayerHint = $order->getCustomer()->getEmail();
        $paymentRequest->Locale = UILocale::HU;
        $paymentRequest->OrderNumber = $order->getNumber();
        $paymentRequest->Currency = Currency::HUF;
        $paymentRequest->RedirectUrl = $urlRedirect; // "http://webshop.example.com/afterpayment";
        $paymentRequest->CallbackUrl = $urlCallback; // "http://webshop.example.com/processpayment";
        $paymentRequest->AddTransaction($trans);

        $paymentIntent = $this->barion->PreparePayment($paymentRequest);
        return $paymentIntent;
    }

    private function createCibPayment(Order $order): ?PaymentResponse
    {
//        $urlRedirect = $this->urlGenerator->generate('site-payment-success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-cib', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlCallback = $this->urlGenerator->generate('site-payment-callback-barion', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $paymentRequest = new PaymentRequest();
        $paymentRequest->uid = 'CIB12345678';
        $paymentRequest->amount = $order->getSummary()->getTotalAmountToPay();
        $paymentRequest->urlReturn = $urlRedirect;


        $paymentIntent = $this->cib->preparePayment($paymentRequest);
//        dd($paymentIntent);
        if ($paymentIntent->requestSuccessful && $paymentIntent->responseCode === PaymentResponse::SUCCESSFUL_INITIALIZATION) {
            $transaction = new PaymentTransaction();
            $transaction->setKind(PaymentTransaction::KIND_SALE);
            $transaction->setAuthorization($paymentIntent->transactionId);
            $transaction->setGateway(PaymentBuilder::GATEWAY_CIB);
            $transaction->setSourceName(PaymentTransaction::SOURCE_WEB);
            $transaction->setOrder($order);
            $transaction->setAmount($order->getSummary()->getTotalAmountToPay());
            $transaction->setCurrency('HUF');

            $transaction->setStatus(PaymentTransaction::STATUS_PENDING);

            $order->addTransaction($transaction);
            $this->em->persist($order);
            $this->em->flush();
            return $paymentIntent;
        }
        return null;
    }
}