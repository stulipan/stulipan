<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\InventoryWasteItem;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="inv_waste")
 * @ORM\Entity(repositoryClass="App\Repository\InventoryWasteRepository")
 */
class InventoryWaste
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
     * ==== One Waste has Items ====
     * ==== mappedBy="waste" => az WasteItem entitásban definiált 'waste' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="InventoryWasteItem", mappedBy="waste", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="waste_id", nullable=true)
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

    public function setDatum(\DateTime $datum = null)
    {
        $this->datum = $datum;
    }

    /**
     * @return InventoryWasteItem $item
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
     * @param InventoryWasteItem $item
     */
    public function addItem(InventoryWasteItem $item): void
    {
        $this->items->add($item);
    }

    /**
     * @param InventoryWasteItem $item
     */
    public function removeItem(InventoryWasteItem $item): void
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
     * Returns all the Products within a Waste
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
     * Returns all the Categories of products within a Waste
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
    public function countItemsInWaste(): int
    {
        return $this->items->count();
    }

}
