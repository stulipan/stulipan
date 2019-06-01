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
class GeneratedDates
{
    /**
     * @var Collection
     *
     * Assert\NotBlank(message="Válaszd ki a kézbesítés napját.")
     */
    private $dates;
    
    public function __construct()
    {
        $this->dates = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    /**
     * @param DeliveryDateWithIntervals $item
     */
    public function addItem(DeliveryDateWithIntervals $item): void
    {
        $this->dates->add($item);
    }

    /**
     * @param DeliveryDateWithIntervals $item
     */
    public function removeItem(DeliveryDateWithIntervals $item): void
    {
        $this->dates->removeElement($item);
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->dates;
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        return !$this->getItems()->isEmpty();
    }
    
}