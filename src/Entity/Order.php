<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\OrderStatus;
use App\Entity\Product\Product;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_order_2")
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @UniqueEntity("number", message="Már létezik rendelés ezzel a számmal!")
 *
 */
class Order
{
    public const STATUS_CREATED = 'created'; // rendelés létrehozva
    public const STATUS_PAYMENT_PENDING = 'pending'; // fizetésre vár
    public const STATUS_PAYMENT_FAILED = 'failed'; // fizetésre sikertelen
    public const STATUS_PAYMENT_REFUNDED = 'refunded'; // összeg visszafizetve
    
    public const STATUS_SENT = 'sent'; // elküldve, azaz szállítás alatt
    public const STATUS_FULFILLED = 'fulfilled'; // teljesítve
    public const STATUS_RETURNED = 'returned'; // visszaküldve
    
    public const STATUS_REJECTED = 'rejected'; // elutasítva - ezt még nem tudom mikor kell használni
    public const STATUS_DELETED = 'deleted'; // törölve
    
    
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="number", type="string", length=20, nullable=true)
     */
    private $number;

    /**
     * @var OrderStatus|null
     *
     * @ORM\OneToOne(targetEntity="OrderStatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    private $status;

    /**
     * @var User|null
     * @Groups({"orderView", "orderList"})
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
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="customer_firstname", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Hiányzik a vásárló keresztneve!")
     */
    private $firstname;
    
    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="customer_lastname", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Hiányzik a vásárló keresztneve!")
     */
    private $lastname;
    
    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="customer_email", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Hiányzik az email cím!")
     * @Assert\Email(message="Ellenőrizd, hogy helyesen írtad be az email címet!")
     */
    private $email;

    /**
     * @var Recipient|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== One Order has one Recipient ====
     *
     * @ORM\OneToOne(targetEntity="Recipient")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", nullable=false)
     * @ Assert\NotBlank(message="Egy rendelésnek kell legyen címzett.")
     */
    private $recipient;

    /**
     * @var Sender|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== One Order has one Sender ====
     *
     * @ORM\OneToOne(targetEntity="Sender")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=true)
     * @ Assert\NotBlank(message="Egy rendelésnek kell legyen feladó.")
     */
    private $sender;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Nincs uzenet!")
     */
    private $message = '';

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="message_author", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Nincs uzenet alairas!")
     */
    private $messageAuthor = '';

    /**
     * @var OrderItem[]|ArrayCollection|null
     * @Groups({"orderView"})
     *
     * ==== One Order has Items ====
     * ==== mappedBy="order" => az OrderItem entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OrderItem", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="order_id", nullable=true)
     * @Assert\NotBlank(message="Egy rendelésben több tétel lehet.")
     */
    private $items;

    /**
     * @var Shipping|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many Orders have one Shipping => Egy rendeléshez egy Szállítás tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Shipping")
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="Válassz szállítási módot!")
     */
    private $shipping;

    /**
     * @var Payment|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many Orders have one Payment => Egy rendeléshez egy Fizetés tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="Válassz fizetési módot!")
     */
    private $payment;


    /**
     * @var Discount|null
     *
     * ==== Many Orders have one Discount => Egy rendeléshez egy Kedvezmény tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="Discount")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", nullable=true)
     * @ Assert\NotBlank(message="Sok rendeléshez egy Kedvezmény tartozik.")
     */
    private $discount;

    /**
     * @var float|null
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $priceTotal = 0;

    /**
     * @var float|null
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total_after_discount", type="decimal", precision=10, scale=2, nullable=true, options={"default":0})
     */
    private $priceTotalAfterDiscount = 0;
    
    /**
     * @var float|null
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="delivery_fee", type="decimal", precision=10, scale=2, nullable=true, options={"default":0})
     */
    private $deliveryFee = 0;
    
    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="shipping_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a címzett nevét.")
     */
    private $shippingName='';

    /**
     * @var int|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="shipping_phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
     */
    private $shippingPhone;

    /**
     * @var OrderAddress|null
     * @Groups({"orderView", "orderList"})
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
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="billing_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a címzett nevét.")
     */
    private $billingName;

    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="billing_company", type="string", length=255, nullable=true)
     */
    private $billingCompany;

    /**
     * @var int|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="billing_phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
     */
    private $billingPhone;

    /**
     * @var float|null
     *
     * @ORM\Column(name="billing_vat_number", type="string", length=255, nullable=true)
     */
    private $billingVatNumber;

    /**
     * @var OrderAddress|null
     * @Groups({"orderView", "orderList"})
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
     * @var \DateTime|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="delivery_date", type="date", nullable=true)
     *
     */
    private $deliveryDate;


    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
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
     * @return string|null
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
     * @return OrderStatus|null
     */
    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }
    
    /**
     * @param OrderStatus|null $status
     */
    public function setStatus(?OrderStatus $status)
    {
        $this->status = $status;
    }

    /**
     * @param OrderItem $item
     */
    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $item->setOrder($this);
            $this->items->add($item);
        }
    }

    /**
     * @param OrderItem $item
     */
    public function removeItem(OrderItem $item): void
    {
        $this->items->removeElement($item);
    }

    /**
     * @return OrderItem[]|Collection
     */
    public function getItems() //: Collection
    {
        return $this->items; //->getValues();   // t ->getValues() is required because of
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        return !$this->items->isEmpty();
    }
    
    /**
     * Counts the number of items in an order
     *
     * @return int
     */
    public function countItems(): int
    {
        $c = 0;
        foreach ($this->getItems() as $item) {
            if ($item->getId()) {
                $c += 1;
            }
        }
        return $c;
    }
    
    /**
     * Checking if the basket contains the product.
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
     * Return key number of orderItem has product
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
     * @return User|null
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
     * @return null|string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    
    /**
     * @param null|string $firstname
     */
    public function setFirstname(?string $firstname)
    {
        $this->firstname = $firstname;
    }
    
    /**
     * @return null|string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }
    
    /**
     * @param null|string $lastname
     */
    public function setLastname(?string $lastname)
    {
        $this->lastname = $lastname;
    }
    
    /**
     * @return null|string
     */
    public function getFullname(): ?string
    {
        $fullname = $this->firstname.' '.$this->lastname;
        return $fullname;
    }
    
    
    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    /**
     * @param string $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * @return Recipient|null
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
     * @return string|null
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
     * @return string|null
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
     * @return Sender|null
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
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @ param Payment $payment
     */
    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping(): ?Shipping
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     */
    public function setShipping(?Shipping $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Discount|null
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
     * @return float|null
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
     * Get information needed to summarize the basket.
     *
     * @return Summary
     */
    public function getSummary(): Summary
    {
        return new Summary($this);
    }
    
    /**
     * @return float|null
     */
    public function getDeliveryFee(): ?float
    {
        if ($this->deliveryFee === null) { return 0; }
        return (float) $this->deliveryFee;
    }
    
    /**
     * @param float|null $deliveryFee
     */
    public function setDeliveryFee(?float $deliveryFee)
    {
        $this->deliveryFee = $deliveryFee;
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
     * @return string|null
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
     * @return string|null
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
     * @return OrderAddress|null
     */
    public function getShippingAddress(): ?OrderAddress
    {
        return $this->shippingAddress;
    }

    /**
     * @var OrderAddress $address
     */
    public function setShippingAddress(?OrderAddress $address): void
    {
        $this->shippingAddress = $address;
    }

    /**
     * @return string|null
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
     * @return string|null
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
     * @return float|null
     */
    public function getBillingVatNumber(): ?float
    {
        return (float) $this->billingVatNumber;
    }

    /**
     * @var float $vat
     */
    public function setBillingVatNumber(?float $vat): void
    {
        $this->billingVatNumber = $vat;
    }

    /**
     * @return string|null
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
     * @return OrderAddress|null
     */
    public function getBillingAddress(): ?OrderAddress
    {
        return $this->billingAddress;
    }

    /**
     * @var OrderAddress $address
     */
    public function setBillingAddress(?OrderAddress $address): void
    {
        $this->billingAddress = $address;
    }

    /**
     * @return \DateTime|null
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
     * @return string|null
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
    
    /**
     * Checking if delivery date is in the past.
     * Returns 'true' if in the past.
     *
     * @return bool
     */
    public function isDeliveryDateInPast(): bool
    {
        $date = $this->getDeliveryDate();
//        dd((new \DateTime('now +'. GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours')));
//        dd((new \DateTime('now +4 hours'))->diff($date)->format('%r%h'));
//        dd((new \DateTime('now +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+1 day')));
        
        if ($date) {
            /** A '+1 day' azert kell mert az adott datum 00:00 orajat veszi.
             * Ergo, ha feb 6. reggel rendelek delutani idopontra, akkor az mar a multban van!
             * Ugyanis a delutani datum feb 6, 00:00 ora lesz adatbazisban, ami reggelhez kepest a multban van!
             */
            $diff = (new \DateTime('now +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+1 day'));
            if ($diff->days >= 0 && $diff->invert == 0) {
                return false;
            } elseif ($diff->invert == 1) {
                return true;
            }
        }
        return true;
    }

}
