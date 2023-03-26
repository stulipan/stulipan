<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Order;
use App\Services\PaymentBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * This has to be configured in services.yaml
 */
class OrderSetPaymentStatus
{
    private $paymentBuilder;

    public function __construct(PaymentBuilder $paymentBuilder)
    {
        $this->paymentBuilder = $paymentBuilder;
    }

    /**
     * @param LifeCycleEventArgs $args
     */
    public function postLoad(Order $order, LifeCycleEventArgs $args)
    {
        $transaction = $order->getTransaction();
        if ($transaction) {
            $paymentStatus = $this->paymentBuilder->computePaymentStatus($transaction);
            $order->setPaymentStatus($paymentStatus);
        }
    }
}