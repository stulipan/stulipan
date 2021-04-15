<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\PaymentMethod;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Must be configured as Service in services.yaml
 * In fact, all Model classes must be services.
 */
class CheckoutPaymentMethod
{
    /**
     * @var PaymentMethod
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotNull(message="checkout.payment-method-missing")
     */
    private $paymentMethod;

    public function __construct(PaymentMethod $paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethod $paymentMethod
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }
}