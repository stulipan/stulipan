<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\DeliveryDateInterval;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * !!! NINCS HASZNALATBAN !!!
 * Ez volt hasznalva amikor formkent jelentek meg a szallitasi napok
 */
class DeliveryDateNincs
{
    /**
     * @var \DateTime
     *
     * Assert\NotBlank(message="Válaszd ki a kézbesítés napját.")
     */
    private $deliveryDate;

    /**
     * @var DeliveryDateInterval
     * Assert\NotBlank(message="Válassz napszakot.")
     */
    private $deliveryInterval;

//    public function __construct(\DateTime $date = null, ?DeliveryDateInterval $interval)
//    {
//        $this->deliveryDate = $date;
//        $this->deliveryInterval = $interval;
//    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return DeliveryDateInterval
     */
    public function getDeliveryInterval(): ?DeliveryDateInterval
    {
        return $this->deliveryInterval;
    }

    /**
     * @param DeliveryDateInterval $deliveryInterval
     */
    public function setDeliveryInterval(DeliveryDateInterval $deliveryInterval)
    {
        $this->deliveryInterval = $deliveryInterval;
    }

}