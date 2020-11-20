<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\ShippingMethod;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Must be configured as Service in services.yaml
 * In fact, all Model classes must be services.
 */
class CheckoutShippingMethod
{
    /**
     * @var ShippingMethod
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotNull(message="cart.missing-shipping-method")
     */
    private $shippingMethod;

    public function __construct(ShippingMethod $shippingMethod = null)
    {
        $this->shippingMethod = $shippingMethod;
    }

    /**
     * @return ShippingMethod
     */
    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    /**
     * @param ShippingMethod $shippingMethod
     */
    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }
}