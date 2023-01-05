<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Product\Product;
use App\Model\Summary;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_checkout")
 * @ORM\Entity(repositoryClass="App\Repository\CheckoutRepository")
 * @UniqueEntity("number", message="Már létezik rendelés ezzel a számmal!")
 */
class Checkout
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
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="currency", type="string", length=20, nullable=true)
     */
    private $currency;

    /**
     * @var Customer|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== Many Orders belong to one Customer ====
     * ==== inversed By="orders" => a Customer entitásban definiált 'orders' attibútumról van szó; A Ordert így kötjük vissza a Customerhez
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="checkouts")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     * @ Assert\NotBlank(message="Egy rendelésnek kell legyen customer-je.")
     */
    private $customer;
    
    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="customer_email", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Hiányzik az email cím!")
     * @Assert\Email(message="Ellenőrizd, hogy helyesen írtad be az email címet!")
     */
    private $email;

//    /**
//     * @var string|null
//     * @Groups({"orderView", "orderList"})
//     *
//     * @ORM\Column(name="customer_phone", type="string", length=15, nullable=false)
//     * @ Assert\NotBlank(message="Add meg a telefonszámot.")
//     */
//    private $phone;

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
     * @var bool|null
     * @ORM\Column(name="same_as_shipping", type="boolean", nullable=true, options={"default"=false})
     *
     */
    private $sameAsShipping;

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
     * @var CheckoutItem[]|ArrayCollection|null
     * @Groups({"orderView"})
     *
     * ==== One Order has Items ====
     * ==== mappedBy="order" => az OrderItem entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CheckoutItem", mappedBy="checkout", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="checkout_id", nullable=true)
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
     * @var float|null
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="shipping_fee", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $shippingFee;

    /**
     * @var float|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="payment_fee", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $paymentFee;

    /**
     * @var float|null
     * @Groups({"orderView", "orderList"})
     *
     * @ Assert\NotBlank()
     * @ORM\Column(name="scheduling_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $schedulingPrice;

    /**
     * @var float|null
     * @Groups({"orderView", "orderList"})
     *
     * @ Assert\NotBlank()
     * @ORM\Column(name="shipping_fee_discount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $shippingFeeDiscount;

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
     * @var bool|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="is_accepted_terms", type="boolean", nullable=true, options={"default"=false})
     */
    private $isAcceptedTerms;

    /**
     * @var Cart
     *
     * ==== One Checkout has one Cart ====
     * ==== This is the OWNING side ===
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Cart", inversedBy="checkout", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy rendelésnek kell legyen egy kosárja.")  // ??
     * @Assert\Valid()   // ??
     */
    private $cart;

    /**
     * @var Order|null
     *
     * ==== One Checkout has one Order ====
     * ==== This is the OWNING side ===
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Order", inversedBy="checkout")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    private $order;

    private $totalSaving;

//    /**
//     * @var string|null
//     * @Groups({"orderView"})
//     *
//     * @ORM\Column(name="cart_token", type="string", length=50, nullable=true)
//     */
//    private $cartToken;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="token", type="string", length=50, nullable=true)
     */
    private $token;

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
     * @param CheckoutItem $item
     */
    public function addItem(CheckoutItem $item): void
    {
        if (!$this->items->contains($item)) {
            $item->setCheckout($this);
            $this->items->add($item);
        }
    }

    /**
     * @param CheckoutItem $item
     */
    public function removeItem(CheckoutItem $item): void
    {
        $this->items->removeElement($item);
    }

    /**
     * @return CheckoutItem[]|Collection
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

    /**
     * Imports the products (items) AND also the Message & MessageAuthor from the Cart
     *
     * @param Cart $cart
     * @param string $messageNotEnoughStock
     * @return $this
     * @throws Exception
     */
    public function importItemsFromCart(Cart $cart, string $messageNotEnoughStock)
    {
        $newItems = new ArrayCollection();
        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();

            $wasFound = false;
            foreach ($this->getItems() as $checkoutItem) {
                // If product in Cart exists in Checkout, update quantity and price
                if ($checkoutItem->getProduct()->getId() === $product->getId()) {

                    if ($product->hasEnoughStock($cartItem->getQuantity())) {
                        $checkoutItem->setQuantity($cartItem->getQuantity());
                        $checkoutItem->setUnitPrice($product->getSellingPrice());
                    } else {
                        throw new Exception($messageNotEnoughStock);
                    }

                    $wasFound = true;
                    break;  // if product was found, break the foreach loop
                }
            }
            // Product wasn't found in Checkout, then add it to newItems
            if (!$wasFound) {
                $checkoutItem = new CheckoutItem();
                $checkoutItem->setProduct($product);

                if ($product->hasEnoughStock($cartItem->getQuantity())) {
                    $checkoutItem->setQuantity($cartItem->getQuantity());
                    $checkoutItem->setUnitPrice($product->getSellingPrice());
                } else {
                    throw new Exception($messageNotEnoughStock);
                }

                $checkoutItem->setCheckout($this);
                $newItems->add($checkoutItem);
            }
        }

        // Add each of the newItems to Checkout
        foreach ($newItems as $newItem) {
            $this->addItem($newItem);
        }

        // If Cart and Checkout have different amount of items (there are more products in Checkout than in Cart)
        if ($cart->getItems()->count() != $this->getItems()->count()) {
            foreach ($this->getItems() as $checkoutItem) {
                $product = $checkoutItem->getProduct();

                $wasFound = false;
                foreach ($cart->getItems() as $cartItem) {
                    // If product from Checkout isn't in Cart, then remove it
                    if ($product->getId() === $cartItem->getProduct()->getId()) {
                        $wasFound = true;
                    }
                }
                if (!$wasFound) {
                    $this->removeItem($checkoutItem);
//                    $this->em->remove($checkoutItem);
                }
            }
        }

        $this->setMessage($cart->getMessage());
        $this->setMessageAuthor($cart->getMessageAuthor());

        return $this;
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
     * Return key number of CheckoutItem has product
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
        return $this->getTotalAfterSale() + $this->getShippingFeeToPay() + $this->getPaymentFeeToPay() + $this->getSchedulingPrice();
    }

    /**
     * @return Customer|null
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     */
    public function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    /**
     * @param string|null $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

//    /**
//     * @return string|null
//     */
//    public function getPhone(): ?string
//    {
//        return $this->phone;
//    }
//
//    /**
//     * @param string|null $phone
//     */
//    public function setPhone(?string $phone): void
//    {
//        $this->phone = $phone;
//    }

    /**
     * @return Recipient|null
     */
    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    /**
     * @param Recipient|null $recipient
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
        return null !== $this->getRecipient();
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
     * @param string|null $author
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
     * @param Sender|null $sender
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
     * @return bool|null
     */
    public function isSameAsShipping(): ?bool
    {
        return $this->sameAsShipping;
    }

    /**
     * @param bool|null $sameAsShipping
     */
    public function setSameAsShipping(?bool $sameAsShipping): void
    {
        $this->sameAsShipping = $sameAsShipping;
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
     * @return float|null
     */
    public function getShippingFee(): ?float
    {
        if ($this->shippingFee === null) { return (float) 0; }
        return (float) $this->shippingFee;
    }

    /**
     * @param float|null $shippingFee
     */
    public function setShippingFee($shippingFee): void
    {
        $this->shippingFee = $shippingFee;
    }

    /**
     * @return bool
     */
    public function hasShippingFee(): bool
    {
        if ($this->shippingFee !== null) {
            return true;
        }
        return false;
    }

    /**
     * @return float|null
     */
    public function getPaymentFee(): ?float
    {
        if ($this->paymentFee === null) { return (float) 0; }
        return (float) $this->paymentFee;
    }

    /**
     * @param float|null $paymentFee
     */
    public function setPaymentFee(?float $paymentFee): void
    {
        $this->paymentFee = $paymentFee;
    }

    /**
     * @return bool
     */
    public function hasPaymentFee(): bool
    {
        if ($this->paymentFee !== null) {
            return true;
        }
        return false;
    }

    public function getShippingFeeToPay(): ?float
    {
//        return (float) ($this->getShippingFee() - $this->getShippingFeeDiscount());
        return (float) ($this->getShippingFee());
    }

    public function getPaymentFeeToPay(): ?float
    {
        return (float) ($this->getPaymentFee());
    }

    /**
     * @return float|null
     */
    public function getSchedulingPrice(): ?float
    {
        if ($this->schedulingPrice === null) { return (float) 0; }
        return (float) $this->schedulingPrice;
    }

    /**
     * @param float|null $schedulingPrice
     */
    public function setSchedulingPrice(?float $schedulingPrice): void
    {
        $this->schedulingPrice = $schedulingPrice;
    }

    /**
     * @return DateTime|null
     */
    public function getDeliveryDate(): ?DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param DateTime|null $deliveryDate
     */
    public function setDeliveryDate(?DateTime $deliveryDate)
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
     * @return bool|null
     */
    public function IsAcceptedTerms(): ?bool
    {
        return $this->isAcceptedTerms;
    }

    /**
     * @param bool|null $isAcceptedTerms
     */
    public function setIsAcceptedTerms(?bool $isAcceptedTerms): void
    {
        $this->isAcceptedTerms = $isAcceptedTerms;
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
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order|null $order
     */
    public function setOrder(?Order $order): void
    {
        $this->order = $order;
    }

//    /**
//     * @return string|null
//     */
//    public function getCartToken(): ?string
//    {
//        return $this->cartToken;
//    }
//
//    /**
//     * @param string|null $cartToken
//     */
//    public function setCartToken(?string $cartToken): void
//    {
//        $this->cartToken = $cartToken;
//    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
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
        if (!$this->getDeliveryDate()) return null;

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
        if ($this->getStatus() && $this->getStatus()->getShortcode() === OrderStatus::ORDER_CREATED) {
            return $this->isDeliveryDateInPast();
        } else {
            return false;
        }
    }


//    /**
//     * Get information needed to summarize the basket.
//     *
//     * @return Summary
//     */
//    public function getSummary(): Summary
//    {
//        return new Summary($this);
//    }
}
