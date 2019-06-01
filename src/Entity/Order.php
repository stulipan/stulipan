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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_order_2")
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @UniqueEntity("number", message="Már létezik rendelés ezzel a számmal!")
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
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=20, nullable=false)
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", length=5, nullable=true)
     * @ Assert\NotBlank(message="Válassz egy állapotot.")
     */
    private $status;

    /**
     * @var User|null
     *
     * ==== Many Orders belong to one Customer ====
     * ==== inversed By="orders" => a User entitásban definiált 'orders' attibútumról van szó; A Ordert így kötjük vissza a Customerhez
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="orders")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ Assert\NotBlank(message="Egy rendelésnek kell legyen customer-je.")
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

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $priceTotal = 0;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total_after_discount", type="decimal", precision=10, scale=2, nullable=true, options={"default":0})
     */
    private $priceTotalAfterDiscount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a címzett nevét.")
     */
    private $shippingName='';

    /**
     * @var int
     *
     * @ORM\Column(name="shipping_phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
     */
    private $shippingPhone;

    /**
     * @var OrderAddress
     *
     * ==== One Order has one Shipping Address ====
     *
     * @ORM\OneToOne(targetEntity="OrderAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy rendelésnek kell legyen egy szállítási címe.")
     * @Assert\Valid()
     */
    private $shippingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a címzett nevét.")
     */
    private $billingName;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_company", type="string", length=255, nullable=true)
     */
    private $billingCompany;

    /**
     * @var int
     *
     * @ORM\Column(name="billing_phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
     */
    private $billingPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_vat_number", type="string", length=255, nullable=true)
     */
    private $billingVatNumber;

    /**
     * @var OrderAddress
     *
     * ==== One Order has one Billing Address ====
     *
     * @ORM\OneToOne(targetEntity="OrderAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy rendelésnek kell legyen egy számlázási címe.")
     * @Assert\Valid()
     */
    private $billingAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_date", type="date", nullable=true)
     *
     */
    private $deliveryDate;


    /**
     * @var string
     *
     * @ORM\Column(name="delivery_interval", type="string", length=50, nullable=true)
     */
    private $deliveryInterval;


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
     * @return string
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(?string $number)
    {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
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
     * @return bool
     */
    public function hasItems(): bool
    {
        return !$this->getItems()->isEmpty();
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
    public function setCustomer(?User $customer): void
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
    public function getPriceTotalAfterDiscount(): ?float
    {
        return (float) $this->priceTotalAfterDiscount;
    }

    /**
     * @param float $priceTotal
     */
    public function setPriceTotalAfterDiscount(float $priceTotal): void
    {
        $this->priceTotalAfterDiscount = $priceTotal;
    }

    /**
     * @return int
     */
    public function CountItemsInCart(): int
    {
          dump($this->getItems()); die;
//        return $this->itemsTotal;
    }


    /**
     * @return string
     */
    public function getShippingName(): ?string
    {
        return $this->shippingName;
    }

    /**
     * @var string $name
     */
    public function setShippingName(?string $name): void
    {
        $this->shippingName = $name;
    }

    /**
     * @return string
     */
    public function getShippingPhone(): ?string
    {
        return $this->shippingPhone;
    }

    /**
     * @var string $phone
     */
    public function setShippingPhone(?string $phone)
    {
        $this->shippingPhone = $phone;
    }

    /**
     * @return OrderAddress
     */
    public function getShippingAddress(): ?OrderAddress
    {
        return $this->shippingAddress;
    }

    /**
     * @var OrderAddress $address
     */
    public function setShippingAddress(OrderAddress $address): void
    {
        $this->shippingAddress = $address;
    }

    /**
     * @return string
     */
    public function getBillingName(): ?string
    {
        return $this->billingName;
    }

    /**
     * @var string $name
     */
    public function setBillingName(?string $name): void
    {
        $this->billingName = $name;
    }

    /**
     * @return string
     */
    public function getBillingCompany(): ?string
    {
        return $this->billingCompany;
    }

    /**
     * @var string $company
     */
    public function setBillingCompany(?string $company): void
    {
        $this->billingCompany = $company;
    }

    /**
     * @return string
     */
    public function getBillingVatNumber(): ?string
    {
        return $this->billingVatNumber;
    }

    /**
     * @var string $vat
     */
    public function setBillingVatNumber(?string $vat): void
    {
        $this->billingVatNumber = $vat;
    }

    /**
     * @return string
     */
    public function getBillingPhone(): ?string
    {
        return $this->billingPhone;
    }

    /**
     * @var string $phone
     */
    public function setBillingPhone(?string $phone)
    {
        $this->billingPhone = $phone;
    }

    /**
     * @return OrderAddress
     */
    public function getBillingAddress(): ?OrderAddress
    {
        return $this->billingAddress;
    }

    /**
     * @var OrderAddress $address
     */
    public function setBillingAddress(OrderAddress $address): void
    {
        $this->billingAddress = $address;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate(): ?\DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     */
    public function setDeliveryDate(\DateTime $deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return string
     */
    public function getDeliveryInterval(): ?string
    {
        return $this->deliveryInterval;
    }

    /**
     * @param string $deliveryInterval
     */
    public function setDeliveryInterval(?string $deliveryInterval): void
    {
        $this->deliveryInterval = $deliveryInterval;
    }

}
