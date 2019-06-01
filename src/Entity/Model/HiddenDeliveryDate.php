<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\DeliveryDateInterval;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class HiddenDeliveryDate
{
    /**
     * @var string
     * @Assert\NotNull(message="Jelöld meg a szállítási napot.")
     */
    private $deliveryDate;

    /**
     * @var string
     * @Assert\Expression("this.notDeliveryInterval()", message = "Válaszd ki, mely napszakban történjen meg a kiszállítás.")
     *
     */
    private $deliveryInterval;

    public function __construct($date = null, string $interval = null)
    {
        if ($date instanceof \Datetime) {
            $this->deliveryDate = $date->format('Y-m-d');
        } else {
            $this->deliveryDate = $date;
        }

        $this->deliveryInterval = $interval;
    }

    /**
     * @return string|null
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
     * @return string|null
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

    /**
     *
     */
    public function notDeliveryInterval()
    {
        if ($this->deliveryDate && !$this->deliveryInterval) {
            return false;
        } else {
            return true;
        }
    }


}