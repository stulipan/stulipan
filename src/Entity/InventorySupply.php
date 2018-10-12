<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\InventorySupplyItem;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="inv_supply")
 * @ORM\Entity
 */
class InventorySupply
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Válassz dátumot.")
     * @ORM\Column(name="datum", type="date", nullable=false)
     */
    private $datum;

    /**
     * @var Collection
     *
     * ==== One Supply has Items ====
     * ==== mappedBy="supply" => az SupplyItem entitásban definiált 'supply' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="InventorySupplyItem", mappedBy="supply", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="supply_id", nullable=true)
     * @Assert\NotBlank(message="Egy áruszállítmányban több tétel lehet.")
     */
    private $items;
    
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatum()
    {
        return $this->datum;
    }

    public function setDatum(\DateTime $datum = null)
    {
        $this->datum = $datum;
    }

    /**
     * @param InventorySupplyItem $item
     */
    public function addItem(InventorySupplyItem $item): void
    {
        $this->items->add($item);
    }

    /**
     * @param InventorySupplyItem $item
     */
    public function removeItem(InventorySupplyItem $item): void
    {
        $this->items->removeElement($item);
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function CountItemsInSupply(): int
    {
          dump($this->getItems()); die;
//        return $this->itemsTotal;
    }

}
