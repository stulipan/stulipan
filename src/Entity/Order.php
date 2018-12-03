<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\Payment;
use App\Entity\Shipping;
use App\Entity\Discount;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Egulias\EmailValidator\Warning\AddressLiteral;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_order_2")
 * @ORM\Entity
 */
class Order
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
     * @var User
     *
     * ==== Many Orders belong to one Customer ====
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy rendelésnek kell legyen customer-je.")
     */
    private $customer;

    /**
     * @var Recipient
     *
     * ==== One Order has one Recipient ====
     *
     * @ORM\OneToOne(targetEntity="Recipient")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=false)
     * @ Assert\NotBlank(message="Egy rendelésnek kell legyen címzett.")
     */
    private $recipient;

    /**
     * @var Sender
     *
     * ==== One Order has one Sender ====
     *
     * @ORM\OneToOne(targetEntity="Sender")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=true)
     * @ Assert\NotBlank(message="Egy rendelésnek kell legyen feladó.")
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Nincs uzenet!")
     */
    private $message = '';

    /**
     * @var string
     *
     * @ORM\Column(name="message_author", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Nincs uzenet alairas!")
     */
    private $messageAuthor = '';

    /**
     * @var Collection
     *
     * ==== One Order has Items ====
     * ==== mappedBy="order" => az OrderItem entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="order_id", nullable=true)
     * @Assert\NotBlank(message="Egy rendelésben több tétel lehet.")
     */
    private $items;

    /**
     * @var Shipping
     *
     * ==== Many Orders have one Shipping => Egy rendeléshez egy Szállítás tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Shipping")
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="Válassz szállítási módot!")
     */
    private $shipping;

    /**
     * @var Payment
     *
     * ==== Many Orders have one Payment => Egy rendeléshez egy Fizetés tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="Válassz fizetési módot!")
     */
    private $payment;


    /**
     * @var Discount
     *
     * ==== Many Orders have one Discount => Egy rendeléshez egy Kedvezmény tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="Discount")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", nullable=true)
     * @ Assert\NotBlank(message="Sok rendeléshez egy Kedvezmény tartozik.")
     */
    private $discount;


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
     * @param OrderItem $item
     */
    public function addItem(OrderItem $item): void
    {
        $this->items->add($item);
    }

    /**
     * @param OrderItem $item
     */
    public function removeItem(OrderItem $item): void
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
     * @return User
     */
    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    /**
     * @param User $customer
     */
    public function setCustomer(User $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return Recipient
     */
    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    /**
     * @param Recipient $recipient
     */
    public function setRecipient(?Recipient $recipient): void
    {
        dump($recipient);
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessageAuthor(): ?string
    {
        return $this->messageAuthor;
    }

    /**
     * @param string $author
     */
    public function setMessageAuthor(?string $author): void
    {
        $this->messageAuthor = $author;
    }

    /**
     * @return Sender
     */
    public function getSender(): ?Sender
    {
        return $this->sender;
    }

    /**
     * @param Sender $sender
     */
    public function setSender(?Sender $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return mixed
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @ param Payment $payment
     */
    public function setPayment(Payment $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @return Shipping
     */
    public function getShipping(): ?Shipping
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     */
    public function setShipping(Shipping $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Discount
     */
    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    /**
     * @param Discount $discount
     */
    public function setDiscount(Discount $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return int
     */
    public function CountItemsInCart(): int
    {
          dump($this->getItems()); die;
//        return $this->itemsTotal;
    }

}
