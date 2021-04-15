<?php

namespace App\Stulipan\Cashin;

use App\Entity\Order;
use App\Entity\PaymentMethod;
use App\Entity\Transaction;
use App\Stulipan\Cashin\Model\Enumerations\CashinEnvironment;
use App\Stulipan\Cashin\Model\PaymentModel;
use App\Stulipan\GatewayCib\GatewayCibBundle;
use App\Stulipan\GatewayCib\Model\Enumerations\CibEnvironment;
use App\Stulipan\GatewayCib\Model\PaymentRequest;
use App\Stulipan\GatewayCib\Model\PaymentResponse;
use BarionClient;
use BarionEnvironment;
use Currency;
use FundingSourceType;
use ItemModel;
use PaymentTransactionModel;
use PaymentType;
use PreparePaymentRequestModel;
use PreparePaymentResponseModel;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UILocale;

class CashinBundle extends Bundle
{
    public const GATEWAY_BARION = 'barion';
    public const GATEWAY_CIB = 'cib';
    
    private $processor;
    private $urlGenerator;

    private $barion;
    private $cib;

    /**
     * @param string $env The environment to connect to
     */
    function __construct($env = CashinEnvironment::PROD)
    {
//        $this->processor = $processor;

        switch ($env) {

            case CashinEnvironment::TEST:
                // do something
                break;

            case CashinEnvironment::PROD:
            default:
                // do something else
                break;
        }

//        $this->urlGenerator = $urlGenerator;
        $this->barion = $this->getBarion();
        $this->cib = $this->getCib();
    }
    
    public function getBarion()
    {
        $myPosKey = '6d53dfe8c2b04b60b33ecbedd857f6ff'; // Pikk Pakk fiok
        $apiVersion = 2;
        $environment = BarionEnvironment::Test;

        /** @var BarionClient $barionClient */
        $barionClient = new BarionClient($myPosKey, $apiVersion, $environment);
        return $barionClient;
    }

    public function getCib()
    {
        // Cib init
        $myPid = 'YSC0001'; // Maysa Car fiok
        $environment = CibEnvironment::TEST;

        $des = __DIR__ . '/../../../config/cib/' . 'YSC.des';

        $cibClient = new GatewayCibBundle($myPid, $des, $environment);
        return $cibClient;
    }
    
    public function createPayment(string $gateway, Order $order)
    {
        $payment = new PaymentModel();
        
        if ($order->getPaymentMethod()->getShortcode() === self::GATEWAY_BARION) {
            $barionPayment = $this->createBarionPayment($order);

            if ($barionPayment === null) {
                // TODO valami hiba tortent a createBarionPayment kozben, kiirni hizauzenetet
                $payment->setErrorUrl('site-checkout-step3-pickPayment');
            }
            $payment->setRedirectUrl($barionPayment->PaymentRedirectUrl);
        }

        if ($order->getPaymentMethod()->getShortcode() === self::GATEWAY_CIB) {
            $cibPayment = $this->createCibPayment($order);

            if ($cibPayment === null) {
                // TODO valami hiba tortent a createBarionPayment kozben, kiirni hizauzenetet
                $payment->setErrorUrl('site-checkout-step3-pickPayment');
            }
            $payment->setRedirectUrl($cibPayment->paymentRedirectUrl);
        }
    }

    public function createBarionPayment(Order $order, UrlGeneratorInterface $urlGenerator): ?PreparePaymentResponseModel
    {
        $trans = new PaymentTransactionModel();
        $trans->POSTransactionId = $order->getNumber(); //$transaction->getId(); //"TRANS-01";
        $trans->Payee = 'payment@hivjesnyerj.hu';  /////// WEBSHOP BARION FIOK EMAIL CIM
        $trans->Total = $order->getSummary()->getTotalAmountToPay();
        $trans->Currency = Currency::HUF;
        $trans->Comment = "Test transaction containing the product";

        foreach ($order->getItems() as $i) {
            $item = new ItemModel();
            $item->Name = $i->getProduct()->getName();
            $item->Description = $i->getProduct()->getName();
            $item->Quantity = $i->getQuantity();
            $item->Unit = "piece";
            $item->UnitPrice = $i->getUnitPrice();
            $item->ItemTotal = $i->getPriceTotal();
            $item->SKU = $i->getProduct()->getSku();

            $trans->AddItem($item);
        }

        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlCallback = $this->urlGenerator->generate('site-checkout-payment-callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

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
        if ($paymentIntent->RequestSuccessful) {
            $transaction = new Transaction();
            $transaction->setKind(Transaction::KIND_SALE);
            $transaction->setAuthorization($paymentIntent->PaymentId);
            $transaction->setGateway(PaymentMethod::BARION);
            $transaction->setSourceName(Transaction::SOURCE_WEB);
            $transaction->setOrder($order);
            $transaction->setAmount($order->getSummary()->getTotalAmountToPay());
            $transaction->setCurrency('HUF');

            $transaction->setStatus(Transaction::STATUS_PENDING);

            $order->addTransaction($transaction);
            $this->em->persist($order);
            $this->em->flush();
            return $paymentIntent;
        }
        return null;
    }

    private function createCibPayment(Order $order): ?PaymentResponse
    {
//        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlRedirect = $this->urlGenerator->generate('site-checkout-payment-cib', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlCallback = $this->urlGenerator->generate('site-checkout-payment-callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $paymentRequest = new PaymentRequest();
        $paymentRequest->uid = 'CIB12345678';
        $paymentRequest->amount = $order->getSummary()->getTotalAmountToPay();
        $paymentRequest->urlReturn = $urlRedirect;


        $paymentIntent = $this->cib->preparePayment($paymentRequest);
//        dd($paymentIntent);
        if ($paymentIntent->requestSuccessful && $paymentIntent->responseCode === PaymentResponse::SUCCESSFUL_INITIALIZATION) {
            $transaction = new Transaction();
            $transaction->setKind(Transaction::KIND_SALE);
            $transaction->setAuthorization($paymentIntent->transactionId);
            $transaction->setGateway(PaymentMethod::CREDIT_CARD);
            $transaction->setSourceName(Transaction::SOURCE_WEB);
            $transaction->setOrder($order);
            $transaction->setAmount($order->getSummary()->getTotalAmountToPay());
            $transaction->setCurrency('HUF');

            $transaction->setStatus(Transaction::STATUS_PENDING);

            $order->addTransaction($transaction);
            $this->em->persist($order);
            $this->em->flush();
            return $paymentIntent;
        }
        return null;
    }
}