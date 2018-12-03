<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\Order;
use App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="product_subproduct")
 * @ORM\Entity
 */

class Subproduct
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
     * @var Product
     *
     * ==== Many Subproducts belong to one Product ====
     * ==== inversed By="attributes" => a Product entitásban definiált 'attributes' attibútumról van szó; Az attribútumot így kötjük vissza a Producthoz
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="attributes")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
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

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $price = 0;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function setProduct(Product $product): void
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


    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return (float)$this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}