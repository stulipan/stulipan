<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\DeliveryDateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class DeliveryDateWithIntervals
{

    /**
     * @var \DateTime
     */
    private $deliveryDate;

    /**
     * @var Collection
     */
    private $intervals;




    public function __construct()
    {
        $this->intervals = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     */
    public function setDeliveryDate(\DateTime $deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return Collection
     */
    public function getIntervals(): Collection
    {
        return $this->intervals;
    }

    /**
     * @param Collection $intervals
     */
    public function setIntervals(Collection $intervals)
    {
        foreach ($intervals as $item) {
            $this->intervals->add($item);
        }
    }

    /**
     * @param DeliveryDateInterval $item
     */
    public function addInterval(DeliveryDateInterval $item): void
    {
        $this->intervals->add($item);
    }

    /**
     * @param DeliveryDateInterval $item
     */
    public function removeItem(DeliveryDateInterval $item): void
    {
        $this->intervals->removeElement($item);
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->intervals;
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        return !$this->getItems()->isEmpty();
    }
    
}