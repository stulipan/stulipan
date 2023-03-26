<?php

declare(strict_types=1);

namespace App\Entity\Boltzaras;

use App\Entity\TimestampableTrait;
use App\Entity\Boltzaras\InventorySupplyItem;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="inv_supply")
 * @ORM\Entity(repositoryClass="App\Repository\Boltzaras\InventorySupplyRepository")
 */
class InventorySupply
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Válassz dátumot.")
     * @ORM\Column(name="datum", type="date", nullable=false)
     */
    private $datum;

//    /**
//     * @var bool
//     *
//     * @ORM\Column(name="is_waste", type="boolean", nullable=false, options={"default"=0})
//     */
//    private $isWaste = '0';

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

    /**
     * @var Collection
     */
    private $productCategories;
    
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getDatum()
    {
        return $this->datum;
    }

    public function setDatum(DateTime $datum = null)
    {
        $this->datum = $datum;
    }

//    /**
//     * @return bool
//     */
//    public function isWaste(): bool
//    {
//        return $this->isWaste;
//    }
//
//    /**
//     * @param bool $isWaste
//     */
//    public function setIsWaiste(bool $isWaste)
//    {
//        $this->isWaste = $isWaste;
//    }

    /**
     * Returns the Item containing the Product
     * @return InventorySupplyItem $item
     */
    public function getItem(InventoryProduct $product)
    {
        foreach ($this->items as $i => $item) {
            if ($item->getProduct() == $product) {
                return $item;
            }
        }
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
     * Returns all the Products within a Supply
     *
     * @return Collection
     */
    public function getProducts(): Collection
    {
        $products = new ArrayCollection();
        foreach ($this->items as $i => $item) {
            $products->add($item->getProduct());
        }
        return $products;
    }

    /**
     * Returns all the Categories of products within a Supply
     *
     * @return Collection
     */
    public function getProductCategories(): Collection
    {
        $categories = new ArrayCollection();
        foreach ($this->items as $i => $item) {
            if ( !$categories->contains($item->getProduct()->getCategory()) ) {
                $categories->add($item->getProduct()->getCategory());
            }
        }
//        foreach ($this->items as $i => $item) {
//            if ( !$categories->contains($item->getProduct()->getCategory()->getCategoryName()) ) {
//                $categories->add($item->getProduct()->getCategory()->getCategoryName());
//            }
//        }
        return $categories;
    }

    public function setProductCategories(Collection $categories)
    {
        $this->productCategories = $categories;
    }


    public function getItemCountInCategories(): array
    {
        $categories = new ArrayCollection();
        foreach ($this->items as $i => $item) {
            if ( !$categories->contains($item->getProduct()->getCategoryId()) ) {
                $categories->add($item->getProduct()->getCategoryId());
            }
        }

        $itemsInSameCategory = [];
        foreach ($categories as $c => $category) {
            $c = 0;
            foreach ($this->items as $i => $item) {
                if ( $category == $item->getProduct()->getCategoryId()) {
                    $c += 1;
                }
            }
            $itemsInSameCategory[$category] = $c;
        }

        return $itemsInSameCategory;
    }


    /**
     * @return int
     */
    public function countItemsInSupply(): int
    {
        return $this->items->count();
    }

}
