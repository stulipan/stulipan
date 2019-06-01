<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\Order;
use App\Entity\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="product_subproduct")
 * @ORM\Entity
 * @UniqueEntity("sku", message="Ez az SKU kód már használatban!")
 */

class SubproductNincs
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
     * @var string|null
     * @Assert\NotBlank(message="Nem adtál meg SKU-t.")
     * @ORM\Column(name="sku", type="string", length=20, nullable=false)
     */
    private $sku;

    /**
     * @var Product
     *
     * ==== Many Subproducts belong to one Product ====
     * ==== inversed By="attributes" => a Product entitásban definiált 'attributes' attibútumról van szó; Az attribútumot így kötjük vissza a Producthoz
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="subproducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy attribútum kell legyen a terméknek.")
     */
    private $product;

    /**
     * @var ProductAttribute
     *
     * ==== One Subproduct is one Attribute => Egy altermék mindig egy attribútum ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ProductAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Az altermék egy attribútum kell legyen.")
     */
    private $attribute;

//    /**
//     * @var float
//     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false)
////     * @ Assert\NotBlank()
//     * @Assert\Valid()
////     * @ Assert\Type(type="numeric", message="Hibás érték!")
//     * @Assert\Range(min=0, minMessage="A termék ára nem lehet negatív.")
//     */
//    private $price;

    /**
     * @var nincs
     *
     * @ORM\OneToOne(targetEntity="App\Entity\SubproductPrice", inversedBy="subproduct")
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=true)
     * @Assert\Type(type="App\Entity\SubproductPrice")
     * Assert\Valid
     */
    private $price;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\SubproductPrice", mappedBy="subproduct", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=true)
     * Assert\NotBlank(message="Nem adtál meg termékárat.")
     */
    private $prices;

    public function __construct()
    {
//        $this->prices = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getAttribute()->getName();
    }

    /**
     * @return null|string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param null|string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return ProductAttribute
     */
    public function getAttribute(): ProductAttribute
    {
        return $this->attribute;
    }

    /**
     * @param ProductAttribute $attribute
     */
    public function setAttribute(ProductAttribute $attribute): void
    {
        $this->attribute = $attribute;
    }

//    /**
//     * @return SubproductPrice[]|Collection
//     */
//    public function getPrices(): Collection
//    {
//        return $this->prices;
//    }
//
//    /**
//     * @param SubproductPrice $price
//     */
//    public function addPrice(SubproductPrice $price): void
//    {
//        $price->setSubproduct($this);
//        if (!$this->prices->contains($price)) {
//            $this->prices->add($price);
//        }
//    }
//
//    /**
//     * @param SubproductPrice $price
//     */
//    public function removePrice(SubproductPrice $price): void
//    {
//        $item->setSubproduct(null);
//        $this->prices->removeElement($price);
//    }

    /**
     * @return nincs
     */
    public function getPrice(): ?nincs
    {
        return $this->price;
    }

    /**
     * @param nincs $price
     */
    public function setPrice(?nincs $price)
    {
        $this->price = $price;
    }
}