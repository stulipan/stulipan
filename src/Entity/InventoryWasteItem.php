<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\InventoryWaste;
use App\Entity\InventoryProduct;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="inv_waste_item")
 * @ORM\Entity
 */

class InventoryWasteItem
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
     * @var int
     *
     * ==== Many WasteItems in one Waste ====
     * ==== inversed By="items" => az Order entitásban definiált 'items' attibútumról van szó; A Tételt így kötjük vissza a Rendeléshez
     *
     * @ORM\ManyToOne(targetEntity="InventoryWaste", inversedBy="items")
     * @ORM\JoinColumn(name="waste_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy termék kell legyen a rendelésben.")
     */
    private $waste;

    /**
     * @var InventoryProduct
     *
     * ==== One WasteItem is one InventoryProduct => Egy tétel mindig egy termék ====
     *
     * @ORM\OneToOne(targetEntity="InventoryProduct")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="A tétel egy termék kell legyen.")
     */
    private $product;

    /**
     * @var int|null
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="A mennyiség nem lehet negatív.")
     * @ORM\Column(name="quantity", type="smallint", nullable=false)
     */
    private $quantity;


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return InventoryWaste
     */
    public function getWaste(): ?InventoryWaste
    {
        return $this->waste;
    }

    /**
     * @var InventoryWaste $waste
     */
    public function setWaste(?InventoryWaste $waste): void
    {
        $this->waste = $waste;
    }

    /**
     * @return InventoryProduct
     */
    public function getProduct(): ?InventoryProduct
    {
        return $this->product;
    }


    /**
     * @var InventoryProduct $product
     */
    public function setProduct(?InventoryProduct $product): void
    {
        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @var int $quantity
     */
    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

}