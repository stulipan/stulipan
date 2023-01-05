<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\Product\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_item")
 * @ORM\Entity
 */

class CartItem
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"cartView", "cartList"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Cart
     *
     * ==== Many CartItems in one Cart ====
     * ==== inversed By="items" => az Cart entitásban definiált 'items' attibútumról van szó; A Tételt így kötjük vissza a Kosarhoz
     *
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="items")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy termék kell legyen a kosárban.")
     */
    private $cart;

    /**
     * @var Product
     * @Groups({"cartView", "cartList"})
     *
     * ==== One CartItem is one Product => Egy tétel mindig egy termék ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="A tétel egy termék kell legyen.")
     */
    private $product;

    /**
     * @var int|null
     * @Groups({"cartView", "cartList"})
     *
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
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @param Cart $cart
     */
    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;
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
}