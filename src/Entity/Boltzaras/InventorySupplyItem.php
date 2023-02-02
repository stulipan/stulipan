<?php

declare(strict_types=1);

namespace App\Entity\Boltzaras;

use App\Entity\TimestampableTrait;
use App\Entity\Boltzaras\InventorySupply;
use App\Entity\Boltzaras\InventoryProduct;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="inv_supply_item")
 * @ORM\Entity
 */

class InventorySupplyItem
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
     * @var int
     *
     * ==== Many SupplyItems in one Supply ====
     * ==== inversed By="items" => az Order entitásban definiált 'items' attibútumról van szó; A Tételt így kötjük vissza a Rendeléshez
     *
     * @ORM\ManyToOne(targetEntity="InventorySupply", inversedBy="items")
     * @ORM\JoinColumn(name="supply_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy termék kell legyen a rendelésben.")
     */
    private $supply;

    /**
     * @var InventoryProduct
     *
     * ==== One SupplyItem is one InventoryProduct => Egy tétel mindig egy termék ====
     *
     * @ORM\ManyToOne(targetEntity="InventoryProduct")
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
     * @var float|null
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="cog", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $cog;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="markup", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $markup;

    /**
     * @ var float
     * @ Assert\NotBlank()
     * @ A ssert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="retail_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $retailPrice;


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return InventorySupply
     */
    public function getSupply(): ?InventorySupply
    {
        return $this->supply;
    }

    /**
     * @var InventorySupply $supply
     */
    public function setSupply(?InventorySupply $supply): void
    {
        $this->supply = $supply;
    }

    /**
     * @return InventoryProduct
     */
    public function getProduct(): ?InventoryProduct
    {
        return $this->product;
    }

//    public function getProductName()
//    {
//        return $this->product->getProductName();
//    }

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

    public function getCog()
    {
        return $this->cog;
    }

    /**
     * @param float|null $cog
     */
    public function setCog($cog)
    {
        $this->cog = $cog;
    }

    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * @param float|null $markup
     */
    public function setMarkup($markup)
    {
        $this->markup = $markup;
    }

    /**
     * @return float
     */
    public function getRetailPrice()
    {
        return (float) $this->retailPrice;
    }

    /**
     * @param float|null $retailPrice
     */
    public function setRetailPrice($retailPrice)
    {
        $this->retailPrice = $retailPrice;
    }
}