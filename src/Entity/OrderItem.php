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
 * @ORM\Table(name="cart_order_item")
 * @ORM\Entity
 */

class OrderItem
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
     * @var Order
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
     * @var Product
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many OrderItems can exist out of one Product ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="A tétel egy termék kell legyen.")
     */
    private $product;

//    /**
//     * @var string
//     * @ORM\Column(name="subproduct_attribute", type="string", length=100, nullable=false)
//     */
//    private $subproductAttribute;

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
     * @var float
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $priceTotal = 0;

    /**
     * @var float
     * @ Assert\NotBlank()
     * @ORM\Column(name="price_total_after_discount", type="decimal", precision=10, scale=2, nullable=true, options={"default":0})
     */
    private $priceTotalAfterDiscount = 0;

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
     * @return OrderItem
     */
    public function setProduct(Product $product): OrderItem
    {
        $this->product = $product;
        return $this;
    }

//    /**
//     * @return string
//     */
//    public function getSubproductAttribute(): ?string
//    {
//        return $this->subproductAttribute;
//    }
//
//    /**
//     * @param string $name
//     */
//    public function setSubproductAttribute(string $name): void
//    {
//        $this->subproductAttribute = $name;
//    }

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

    /**
     * @return float
     */
    public function getPriceTotal(): float
    {
        return (float) $this->priceTotal;
    }

    /**
    * @param float $priceTotal
    */
    public function setPriceTotal(float $priceTotal): void
    {
        $this->priceTotal = $priceTotal;
    }

    /**
     * @return float
     */
    public function getPriceTotalAfterDiscount(): float
    {
        return $this->priceTotalAfterDiscount;
    }

    /**
     * @param float $priceTotal
     */
    public function setPriceTotalAfterDiscount(float $priceTotal): void
    {
        $this->priceTotalAfterDiscount = $priceTotal;
    }
}