<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\Order;
use App\Entity\Product\Product;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_checkout_item")
 * @ORM\Entity
 */

class CheckoutItem
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Checkout
     *
     * ==== Many CheckoutItems in one Checkout ====
     * ==== inversed By="items" => az Checkout entitásban definiált 'items' attibútumról van szó; A Tételt így kötjük vissza a Rendeléshez
     *
     * @ORM\ManyToOne(targetEntity="Checkout", inversedBy="items")
     * @ORM\JoinColumn(name="checkout_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy termék kell legyen a checkoutban.")
     */
    private $checkout;

    /**
     * @var Product
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many CheckoutItems can exist out of one Product ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="A tétel egy termék kell legyen.")
     */
    private $product;

    /**
     * @var int|null
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="A mennyiség nem lehet negatív.")
     * @ORM\Column(name="quantity", type="smallint", nullable=false)
     */
    private $quantity;

    /**
     * @var float
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="unit_price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $unitPrice = 0;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Checkout
     */
    public function getCheckout(): Checkout
    {
        return $this->checkout;
    }

    /**
     * @param Checkout $checkout
     */
    public function setCheckout(Checkout $checkout): void
    {
        $this->checkout = $checkout;
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
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): ?float
    {
        return (float) $this->unitPrice;
    }

    /**
     * @param float $unitPrice
     */
    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }
}