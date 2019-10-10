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
     * @Assert\Expression("this.missingDeliveryInterval()", message = "Válaszd ki, mely napszakban történjen meg a kiszállítás.")
     *
     */
    private $deliveryInterval;
    
    /**
     * @var float
     * @Assert\Range(min=0, minMessage="Hiányzik a szállítási díj.")
     */
    private $deliveryFee;

    public function __construct($date = null, string $interval = null, float $fee = null)
    {
        if ($date instanceof \Datetime) {
            $this->deliveryDate = $date->format('Y-m-d');
        } else {
            $this->deliveryDate = $date;
        }

        $this->deliveryInterval = $interval;
        $this->deliveryFee = $fee;
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
    public function missingDeliveryInterval()
    {
        if ($this->deliveryDate && !$this->deliveryInterval) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * @return float
     */
    public function getDeliveryFee(): ?float
    {
        return (float) $this->deliveryFee;
    }
    
    /**
     * @param float $deliveryFee
     */
    public function setDeliveryFee(?float $deliveryFee)
    {
        $this->deliveryFee = $deliveryFee;
    }


}