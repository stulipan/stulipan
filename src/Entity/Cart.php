<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Product\Product;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity
 * @ ORM\Entity(repositoryClass="App\Repository\CartRepository")
 */
class Cart
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"cartView", "cartList"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Groups({"cartView", "cartList"})
     *
     * @ORM\Column(name="currency", type="string", length=20, nullable=true)
     */
    private $currency;

    /**
     * @var string|null
     * @Groups({"cartView"})
     *
     * @ORM\Column(name="note", type="string", length=255, nullable=true)
     */
    private $note;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Nincs uzenet!")
     */
    private $message;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="message_author", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Nincs uzenet alairas!")
     */
    private $messageAuthor;

    /**
     * @var CartItem[]|ArrayCollection|null
     * @Groups({"cartView"})
     *
     * ==== One Cart has Items ====
     * ==== mappedBy="cart" => az CartItem entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CartItem", mappedBy="cart", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="cart_id", nullable=true)
     * @Assert\NotBlank(message="Egy rendelésben több tétel lehet.")
     */
    private $items;

    /**
     * @var Checkout|null
     *
     * ==== One Cart belongs to a Checkout ====
     * ==== This is the INVERSE side ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Checkout", mappedBy="cart")
     */
    private $checkout;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     */
    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     */
    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string|null
     */
    public function getMessageAuthor(): ?string
    {
        return $this->messageAuthor;
    }

    /**
     * @param string|null $messageAuthor
     */
    public function setMessageAuthor(?string $messageAuthor): void
    {
        $this->messageAuthor = $messageAuthor;
    }

    /**
     * @return Checkout|null
     */
    public function getCheckout(): ?Checkout
    {
        return $this->checkout;
    }

    /**
     * @param Checkout|null $checkout
     */
    public function setCheckout(?Checkout $checkout): void
    {
        $this->checkout = $checkout;
    }

    /**
     * @param CartItem $item
     */
    public function addItem(CartItem $item): void
    {
        if (!$this->items->contains($item)) {
            $item->setCart($this);
            $this->items->add($item);
        }
    }

    /**
     * @param CartItem $item
     */
    public function removeItem(CartItem $item): void
    {
        $this->items->removeElement($item);
    }

    /**
     * @return CartItem[]|Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        return !$this->items->isEmpty();
    }

    public function getItemCount()
    {
        $c = 0;
        foreach ($this->getItems() as $item) {
            if ($item->getId()) {
                $c += $item->getQuantity();
            }
        }
        return $c;
    }

    /**
     * Checking if the cart contains the product.
     *
     * @param Product $product
     * @return bool
     */
    public function containsTheProduct(Product $product): bool
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return key number of CartItem has product
     *
     * @param Product $product
     * @return int|null
     */
    public function indexOfProduct(Product $product): ?int
    {
        foreach ($this->items as $key => $item) {
            if ($item->getProduct() === $product) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @Groups({"cartView", "cartList"})
     *
     * @return float|null
     */
    public function getTotalBeforeSale()
    {
        $total = 0;
        foreach ($this->items as $item) {
            if ($item->getProduct()->isOnSale()) {
                $total += $item->getQuantity() * $item->getProduct()->getCompareAtPrice();
            } else {
                $total += $item->getQuantity() * $item->getProduct()->getSellingPrice();
            }
        }
        return $total;
    }

    /**
     * @Groups({"cartView", "cartList"})
     *
     * @return float|null
     */
    public function getTotalAfterSale()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getQuantity() * $item->getProduct()->getSellingPrice();
        }
        return $total;
    }

    /**
     * @Groups({"cartView", "cartList"})
     *
     * @return float|null
     */
    public function getTotalSaving()
    {
        return $this->getTotalBeforeSale() - $this->getTotalAfterSale();
    }

    public function hasProductOnSale()
    {
        // upon finding first onSale product, it returns 'true'
        foreach ($this->items as $item) {
            if ($item->getProduct()->isOnSale()) {
                return true;
            }
        }
        return false;
    }

    public function getTotalAmountToPay()
    {
        return $this->getTotalAfterSale();
    }
}
