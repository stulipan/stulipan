<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\OrderItem;
use App\Entity\OrderStatus;
use App\Entity\Product\Product;
use App\Entity\TimestampableTrait;
use App\Entity\User;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use App\Entity\Discount;

use App\Model\Summary;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Egulias\EmailValidator\Warning\AddressLiteral;
use phpDocumentor\Reflection\Types\This;
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
     * @var PaymentStatus|null
     *
     * @ORM\OneToOne(targetEntity="PaymentStatus")
     * @ORM\JoinColumn(name="payment_status_id", referencedColumnName="id", nullable=true)
     */
    private $paymentStatus;

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
     * @var ShippingMethod|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many Orders have one Shipping => Egy rendeléshez egy Szállítás tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ShippingMethod")
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="Válassz szállítási módot!")
     */
    private $shippingMethod;

    /**
     * @var PaymentMethod|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many Orders have one Payment => Egy rendeléshez egy Fizetés tartozik ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentMethod")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="Válassz fizetési módot!")
     */
    private $paymentMethod;


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
     * @ORM\Column(name="price_total_", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $priceTotal = 0;

    /**
     * @var float|null
     * @Assert\NotBlank()
     * @ORM\Column(name="price_total_after_discount_", type="decimal", precision=10, scale=2, nullable=true, options={"default":0})
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
     * @ORM\Column(name="shipping_firstname", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a keresztnevet.")
     */
    private $shippingFirstname;

    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="shipping_lastname", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a vezetéknevet.")
     */
    private $shippingLastname;

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
     * @ORM\Column(name="billing_firstname", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a keresztnevet.")
     */
    private $billingFirstname;

    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="billing_lastname", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a vezetéknevet.")
     */
    private $billingLastname;

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
     * @var string|null
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
     * @var DateTime|null
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

    /**
     * @var ClientDetails
     *
     * ==== One Order has one ClientDetails ====
     *
     * @ORM\OneToOne(targetEntity="ClientDetails", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="client_details_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy rendelésnek kell legyen egy ClientDetails.")
     * @Assert\Valid()
     */
    private $clientDetails;

    /**
     * @var Transaction[]|ArrayCollection|null;
     * @Groups({"orderView"})
     *
     * ==== One Order has several Transactions ====
     * ==== mappedBy="order" => a Transaction entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="order_id", nullable=true)
     * @ORM\OrderBy({"createdAt"="ASC", "id"="ASC"})
     * @Assert\NotBlank(message="Egy rendelésnek több tranzakciója lehet.")
     */
    private $transactions;

    /**
     * @var OrderLog[]|ArrayCollection|null
     * @Groups({"orderView"})
     *
     * ==== One Order has History entries ====
     * ==== mappedBy="order" => az OrderLog entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OrderLog", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="order_id", nullable=true)
     * @ORM\OrderBy({"createdAt"="DESC", "id"="DESC"})
     * @Assert\NotBlank(message="Egy rendelésnek több előzménye lehet.")
     */
    private $logs;


    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->logs = new ArrayCollection();
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
     * @return PaymentStatus|null
     */
    public function getPaymentStatus(): ?PaymentStatus
    {
        return $this->paymentStatus;
    }

    /**
     * @param PaymentStatus|null $paymentStatus
     */
    public function setPaymentStatus(?PaymentStatus $paymentStatus): void
    {
        $this->paymentStatus = $paymentStatus;
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
    public function itemsCount(): int
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
        $this->firstname = ucwords($firstname);
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
        $this->lastname = ucwords($lastname);
    }
    
    /**
     * @return null|string
     */
    public function getFullname(): ?string
    {
        $fullname = $this->firstname.' '.$this->lastname;
        return ucwords($fullname);
    }

    /**
     * @return null|string
     */
    public function getInitials(): ?string
    {
        $fullnameInitial = $this->firstname[0].$this->lastname[0];
        return ucwords($fullnameInitial);
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
     * @return bool
     */
    public function hasRecipient(): bool
    {
        return null === $this->getRecipient() ? false : true;
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
     * @return bool
     */
    public function hasSender(): bool
    {
        return null === $this->getSender() ? false : true;
    }

    /**
     * @return PaymentMethod|null
     */
    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethod|null $paymentMethod
     */
    public function setPaymentMethod(?PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return ShippingMethod|null
     */
    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    /**
     * @param ShippingMethod $shippingMethod
     */
    public function setShippingMethod(?ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
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
        if ($this->deliveryFee === null) { return (float) 0; }
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
    public function getShippingFirstname(): ?string
    {
        return $this->shippingFirstname;
    }

    /**
     * @var null|string $name
     */
    public function setShippingFirstname(?string $name): void
    {
        $this->shippingFirstname = $name ? ucwords($name) : $name;
    }

    /**
     * @return string|null
     */
    public function getShippingLastname(): ?string
    {
        return $this->shippingLastname;
    }

    /**
     * @var null|string $name
     */
    public function setShippingLastname(?string $name): void
    {
        $this->shippingLastname = $name ? ucwords($name) : $name;
    }

    /**
     * @return string|null
     */
    public function getShippingFullname(): ?string
    {
        if ($this->shippingLastname && $this->shippingFirstname) {
            return $this->shippingLastname.' '.$this->shippingFirstname;
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getShippingPhone(): ?string
    {
        return $this->shippingPhone;
    }

    /**
     * @var null|string $phone
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
    public function getBillingFirstname(): ?string
    {
        return $this->billingFirstname;
    }

    /**
     * @var null|string $name
     */
    public function setBillingFirstname(?string $name): void
    {
        $this->billingFirstname = ucwords($name);
    }

    /**
     * @return string|null
     */
    public function getBillingLastname(): ?string
    {
        return $this->billingLastname;
    }

    /**
     * @var null|string $name
     */
    public function setBillingLastname(?string $name): void
    {
        $this->billingLastname = ucwords($name);
    }

    /**
     * @return string|null
     */
    public function getBillingFullname(): ?string
    {
        if ($this->billingLastname && $this->billingFirstname) {
            return $this->billingLastname.' '.$this->billingFirstname;
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getBillingCompany(): ?string
    {
        return $this->billingCompany;
    }

    /**
     * @var null|string $company
     */
    public function setBillingCompany(?string $company): void
    {
        $this->billingCompany = $company;
    }

    /**
     * @return string|null
     */
    public function getBillingVatNumber(): ?string
    {
        return $this->billingVatNumber;
    }

    /**
     * @var string|null $vat
     */
    public function setBillingVatNumber(?string $vat): void
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
     * @return DateTime|null
     */
    public function getDeliveryDate(): ?DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param DateTime $deliveryDate
     */
    public function setDeliveryDate(DateTime $deliveryDate)
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
     * @return ClientDetails
     */
    public function getClientDetails(): ?ClientDetails
    {
        return $this->clientDetails;
    }

    /**
     * @param ClientDetails $clientDetails
     */
    public function setClientDetails(ClientDetails $clientDetails): void
    {
        $this->clientDetails = $clientDetails;
    }

    /**
     * @param OrderLog $log
     */
    public function addLog(OrderLog $log): void
    {
        if (!$this->logs->contains($log)) {
            $log->setOrder($this);
            $this->logs->add($log);
        }
    }

    /**
     * @param OrderLog $log
     */
    public function removeLog(OrderLog $log): void
    {
        $this->logs->removeElement($log);
    }

    /**
     * @return OrderLog[]|Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return bool
     */
    public function hasLogs(): bool
    {
        return !$this->logs->isEmpty();
    }
    
    /**
     * Checking if delivery date is in the past.
     * Returns 'true' if in the past.
     *
     * @return bool
     */
    public function isDeliveryDateInPast(): bool
    {
        $date = clone $this->getDeliveryDate();
//        dd((new \DateTime('now +'. GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours')));
//        dd((new \DateTime('now +4 hours'))->diff($date)->format('%r%h'));
//        dd((new \DateTime('now +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+1 day')));
        if ($date) {
            /** A '+1 day' azert kell mert az adott datum 00:00 orajat veszi.
             * Ergo, ha feb 6. reggel rendelek delutani idopontra, akkor az mar a multban van!
             * Ugyanis a delutani datum feb 6, 00:00 ora lesz adatbazisban, ami reggelhez kepest a multban van!
             */
//            $diff = (new DateTime('today +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+0 day'));
            $diff = (new DateTime('today'))->diff($date->modify('+0 day'));
            if ($diff->days >= 0 && $diff->invert == 0) {
                return false;
            } elseif ($diff->invert == 1) {
                return true;
            }
        }
        return true;
    }

    public function getDeliveryOverdueDays(): ?int
    {
        $date = clone $this->getDeliveryDate();
        /** DeliveryOverdueDays are calculated only for 'Open' and 'Paid' orders */
        if (!$this->isClosed() && $this->isPaid()) {
            if ($date) {
                /** A '+1 day' azert kell mert az adott datum 00:00 orajat veszi.
                 * Ergo, ha feb 6. reggel rendelek delutani idopontra, akkor az mar a multban van!
                 * Ugyanis a delutani datum feb 6, 00:00 ora lesz adatbazisban, ami reggelhez kepest a multban van!
                 */
//            $diff = (new DateTime('today +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+0 day'));
                $diff = (new DateTime('today'))->diff($date->modify('+0 day'));
                if ($diff->days >= 0 && $diff->invert == 0) {
                    return $diff->days;
                } elseif ($diff->invert == 1) {
                    return -$diff->days;
                }
            }
        }
        return null;
    }

    public function isDeliveryOverdue(): bool
    {
        if ($this->getStatus() && $this->getStatus()->getShortcode() === OrderStatus::STATUS_CREATED) {
            return $this->isDeliveryDateInPast();
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        if ($this->getStatus() && (
                $this->getStatus()->getShortcode() === OrderStatus::STATUS_PAYMENT_REFUNDED ||
                $this->getStatus()->getShortcode() === OrderStatus::STATUS_FULFILLED ||
                $this->getStatus()->getShortcode() === OrderStatus::STATUS_DELETED
            )) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isFulfilled(): bool
    {
        if ($this->getStatus() && $this->getStatus()->getShortcode() === OrderStatus::STATUS_FULFILLED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isUnpaid(): bool
    {
        if ($this->getPaymentStatus() && (
                $this->getPaymentStatus()->getShortcode() === PaymentStatus::STATUS_PENDING ||
                $this->getPaymentStatus()->getShortcode() === PaymentStatus::STATUS_PARTIALLY_PAID)
            ) {
            return true;
        } else {
            return false;
        }
    }

    public function isPaid(): bool
    {
        if ($this->getPaymentStatus()->getShortcode() === PaymentStatus::STATUS_PAID) {
            return true;
        }
        return false;
    }

    public function isBankTransfer(): bool
    {
        if ($this->getPaymentMethod() && $this->getPaymentMethod()->isBankTransfer()) {
            return true;
        } else {
            return false;
        }
    }

    public function hasComment(): bool
    {
        foreach ($this->logs as $log) {
            if ($log->isComment()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Transaction[]|Collection|null
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param Transaction $transaction
     */
    public function addTransaction(Transaction $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $transaction->setOrder($this);
            $this->transactions->add($transaction);
        }
    }

    /**
     * @param Transaction $transaction
     */
    public function removeTransaction(Transaction $transaction): void
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * @return bool
     */
    public function hasTransactions(): bool
    {
        return !$this->transactions->isEmpty();
    }

}
