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
 * @ORM\Table(name="cart_order_item")
 * @ORM\Entity
 */

class OrderItem
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
     * ==== Many OrderItems in one Order ====
     * ==== inversed By="items" => az Order entitásban definiált 'items' attibútumról van szó; A Tételt így kötjük vissza a Rendeléshez
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy termék kell legyen a rendelésben.")
     */
    private $order;

    /**
     * @var int
     *
     * ==== One OrderItem is one Product => Egy tétel mindig egy termék ====
     *
     * @ORM\OneToOne(targetEntity="Product")
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
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $price = 0;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $priceTotal = 0;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
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
    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPriceTotal(): float
    {
        return $this->priceTotal;
    }

    /**
    * @param float $priceTotal
    */
    public function setPriceTotal(float $priceTotal): void
    {
        $this->priceTotal = $priceTotal;
    }
}