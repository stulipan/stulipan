<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\DeliveryDateInterval;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class DeliveryDate
{
    /**
     * @var string
     */
    private $deliveryDate;

    /**
     * @var string
     */
    private $deliveryInterval;

    /**
     * @return string
     */
    public function getDeliveryDate(): ?string
    {
        return $this->deliveryDate;
    }

    /**
     * @param string $deliveryDate
     */
    public function setDeliveryDate(?string $deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return string
     */
    public function getDeliveryInterval(): ?string
    {
        return $this->deliveryInterval;
    }

    /**
     * @param string $deliveryInterval
     */
    public function setDeliveryInterval(?string $deliveryInterval)
    {
        $this->deliveryInterval = $deliveryInterval;
    }
}