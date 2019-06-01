<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Model\CustomerBasic;
use App\Entity\Model\Message;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderSessionStorage;
use App\Entity\Payment;
use App\Entity\Shipping;
use App\Entity\Discount;
use App\Entity\Product\Product;
use App\Entity\Summary;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

use Exception;
use Symfony\Component\HttpFoundation\Session\Session;

class OrderBuilder
{
    /**
     * @var OrderSessionStorage
     */
    private $storage;

    /**
     * @var User
     */
    private $customer;

    /**
     * @var Order
     */
    private $order;
    
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(OrderSessionStorage $storage, EntityManagerInterface $entityManager,
                                EventDispatcherInterface $eventDispatcher)
    {
        $this->storage = $storage;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;

        $this->customer = new User();
        $this->order = $this->getCurrentOrder();
    }

    public function getId(): ?int
    {
        return $this->order->getId();
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param User $customer    Add current user as Customer (to the current Order)
     */
    public function setCustomer(User $customer): void
    {
        $orderBeforeId = $this->order->getId();
        $this->order->setCustomer($customer);

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_CREATED, $event);
        } else {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        }

//        // Run events
//        $event = new GenericEvent($this->order);
//        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);

    }

    /**
     * @param CustomerBasic $customer    Add a CustomerBasic to the current Order
     */
    public function setCustomerBasic(CustomerBasic $customer): void
    {
        $orderBeforeId = $this->order->getId();

        $this->order->setBillingName($customer->getLastname().' '.$customer->getFirstname());
        $this->order->setBillingPhone($customer->getPhone());
        $this->storage->add('email', $customer->getEmail());
        $this->storage->add('firstname', $customer->getFirstname());
        $this->storage->add('lastname', $customer->getLastname());

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_CREATED, $event);
        } else {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        }
    }

        /**
     * @return OrderSessionStorage
     */
    public function getCurrentSession(): OrderSessionStorage
    {
        return $this->storage;
    }

    /**
     * @return Order
     */
    public function getCurrentOrder(): Order
    {
        /**
         * Returns the Order which is in the session, if any
         */
        $order = $this->storage->getOrderById();
        if ($order !== null) {
            return $order;
        }
        /**
         * Creates a new Order (with id!) if there's none in the session
         */
        $newOrder = new Order;

        return $newOrder;
    }

    /**
     * Adding a product to the basket.
     * If the product exists, its quantity is increased.
     *
     * @param Product $product
     * @param integer $quantity
     * @return void
     */
    public function addItem(Product $product, int $quantity): void
    {
        $orderBeforeId = $this->order->getId();
        if (!$this->containsTheProduct($product)) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($this->order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            if ($product->hasSubproducts()) {
                $price = $product->getSelectedSubproduct()->getPrice();
                $orderItem->setPrice($price);
                $orderItem->setSubproductAttribute($product->getSelectedSubproduct()->getName());
            } else {
                $orderItem->setPrice($product->getPrice()->getValue());
            }

            $orderItem->setPriceTotal($orderItem->getPrice() * $orderItem->getQuantity());
            $this->order->addItem($orderItem);
        } else {
            $key = $this->indexOfProduct($product);
            $item = $this->order->getItems()->get($key);
            $quantity = $this->order->getItems()->get($key)->getQuantity() + 1;
            if ($product->hasSubproducts()) {
                $price = $product->getSelectedSubproduct()->getPrice();
                $this->setItemAttribute($item, $product->getSelectedSubproduct()->getName());
            } else {
                $price = $product->getPrice()->getValue();
            }
            $this->setItemPrice($item, $price);
            $this->setItemQuantity($item, $quantity);
        }
//        if ($this->order->getCustomer()->getId() === null) {
//            $this->order->setCustomer(null);
//        } else {
//            $this->order->setCustomer($this->customer);
//        }
//        dd($this);

        if ($this->customer === null) {
            $this->setCustomer($this->order->getCustomer());
        }

//        dd($this);

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_CREATED, $event);
        } else {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        }

    }

    /**
     * Adding an Item to the basket from a previous Order, which hasn't been placed (abandoned cart scenario).
     * Adds only Items which aren't already present in the current Order.
     *
     * !! Átrakom az Itemeket a régi Orderből az újba. A régibe nem maradnak meg !!
     * Ez később baj lehet, amikor elhagyott kosár statisztikát csinálnánk
     *
     *
     * @param OrderItem $item
     * @return void
     */
    public function addItemFromPreviousOrder(OrderItem $orderItem): void
    {
        $orderBeforeId = $this->order->getId();
        if (!$this->containsTheProduct($orderItem->getProduct())) {
            $orderItem->setOrder($this->order);
            $this->order->addItem($orderItem);
        }
        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_CREATED, $event);
        } else {
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        }

    }

    /**
     * @param string|null $deliveryDate
     * @param null|string $deliveryInterval
     */
    public function setDeliveryDate(string $deliveryDate = null, string $deliveryInterval = null): void
    {
        if ($deliveryDate) {
            $deliveryDate = \DateTime::createFromFormat('!Y-m-d', $deliveryDate);
            /**
             * If $deliveryDate equals date in database
             */
            if ($this->order->getDeliveryDate() && $this->order->getDeliveryDate() === $deliveryDate->format('Y-m-d')) {
                // do nothing
            } /**
             * Else update date in database and remove existing interval
             */
            else {
                $this->order->setDeliveryDate($deliveryDate);
                $this->order->setDeliveryInterval(null);
            }
        }
        if ($deliveryInterval) {
            if ($this->order->getDeliveryInterval() && $this->order->getDeliveryInterval() === $deliveryInterval) {
                // do nothing
            } else {
                $this->order->setDeliveryInterval($deliveryInterval);
            }
        }

        // Run events
        $event = new GenericEvent($this->order);
        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
    }

    /**
     * @param Recipient $recipient
     */
    public function setRecipient(Recipient $recipient): void
    {
        $this->order->setRecipient($recipient);
        $this->order->setShippingName($recipient->getName());
        $this->order->setShippingPhone($recipient->getPhone());

        $shippingAddress = new OrderAddress();
        $shippingAddress->setStreet($recipient->getAddress()->getStreet());
        $shippingAddress->setCity($recipient->getAddress()->getCity());
        $shippingAddress->setZip($recipient->getAddress()->getZip());
        $shippingAddress->setProvince($recipient->getAddress()->getProvince());
        $shippingAddress->setCountry($recipient->getAddress()->getCountry());
        $shippingAddress->setAddressType($recipient->getAddress()->getAddressType());

        $this->order->setShippingAddress($shippingAddress);
        // Run events
        $event = new GenericEvent($this->order);
        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
    }

    /**
     * @param Sender $sender
     */
    public function setSender(Sender $sender): void
    {
        $this->order->setSender($sender);
        $this->order->setBillingName($sender->getName());
        $this->order->setBillingCompany($sender->getCompany());
        $this->order->setBillingPhone($sender->getPhone());

        $billingAddress = new OrderAddress();
        $billingAddress->setStreet($sender->getAddress()->getStreet());
        $billingAddress->setCity($sender->getAddress()->getCity());
        $billingAddress->setZip($sender->getAddress()->getZip());
        $billingAddress->setProvince($sender->getAddress()->getProvince());
        $billingAddress->setCountry($sender->getAddress()->getCountry());
        $billingAddress->setAddressType($sender->getAddress()->getAddressType());

        $this->order->setBillingAddress($billingAddress);
        // Run events
        $event = new GenericEvent($this->order);
        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
    }

    /**
     * Checking if the basket contains the product.
     *
     * @param Product $product
     * @return bool
     */
    public function containsTheProduct(Product $product): bool
    {
        foreach ($this->order->getItems() as $item) {
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
        foreach ($this->order->getItems() AS $key => $item) {
            if ($item->getProduct() === $product) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Counts the number of items in an order
     *
     * @return int
     */
    public function countItems(): int
    {
//        $count = [];
        $c = 0;
        foreach ($this->order->getItems() as $item) {
            if ($item->getId()) {
                $c += 1;
            }
//            $count[$item->getId()] = ($count[$item->getId()] ?? 0)+ 1;
        }
//        return count($count);
        return $c;
    }

    /**
     * Update the quantity for an existing product.
     *
     * @param OrderItem $item
     * @param integer $quantity
     * @throws Exception
     */
    public function setItemQuantity(OrderItem $item, int $quantity): void
    {
        if ($this->order && $this->order->getItems()->contains($item)) {
            $key = $this->order->getItems()->indexOf($item);
            $item->setQuantity($quantity);
            $item->setPriceTotal($item->getQuantity()*$item->getPrice());
            $this->order->getItems()->set($key, $item);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Update the price for an existing product.
     * A product may have several prices because of its subproducts.
     *
     * @param OrderItem $item
     * @param integer $price
     * @throws Exception
     */
    public function setItemPrice(OrderItem $item, float $price): void
    {
        if ($this->order && $this->order->getItems()->contains($item)) {
            $key = $this->order->getItems()->indexOf($item);
//            dump($price);die;
            $item->setPrice($price);
            $this->order->getItems()->set($key, $item);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Update the Total Price for an existing product.
     *
     * @param OrderItem $item
     * @param integer $price
     * @throws Exception
     */
    public function setPriceTotal(OrderItem $item, float $price): void
    {
        if ($this->order && $this->order->getItems()->contains($item)) {
            $key = $this->order->getItems()->indexOf($item);
            $item->setPriceTotal($price);
            $this->order->getItems()->set($key, $item);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Update the price for an existing product.
     * A product may have several prices because of its subproducts.
     *
     * @param OrderItem $item
     * @param string $name
     * @throws Exception
     */
    public function setItemAttribute(OrderItem $item, string $name): void
    {
        if ($this->order && $this->order->getItems()->contains($item)) {
            $key = $this->order->getItems()->indexOf($item);
            $item->setSubproductAttribute($name);
            $this->order->getItems()->set($key, $item);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Removing the product from the basket.
     *
     * @param OrderItem $item
     * @throws Exception
     */
    public function removeItem(OrderItem $item): void
    {
        if ($this->order && $this->order->getItems()->contains($item)) {
            $this->order->removeItem($item);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Downloading all products along with information needed on the cart listing.
     *
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->order->getItems();
    }

    /**
     * Set message method
     *
     * @param Message $message
     */
    public function setMessage(?Message $message): void
    {
        if ($this->order) {
            $this->order->setMessage($message->getMessage());
            $this->order->setMessageAuthor($message->getMessageAuthor());
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }


    /**
     * Set payment method
     *
     * @param Payment $payment
     */
    public function setPayment(Payment $payment): void
    {
        if ($this->order) {
            $this->order->setPayment($payment);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Set shipping method
     *
     * @param Shipping $shipping
     */
    public function setShipping(Shipping $shipping): void
    {
        if ($this->order) {
            $this->order->setShipping($shipping);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Set discount code
     *
     * @param Discount $discount
     */
    public function setDiscount(Discount $discount): void
    {
        if ($this->order) {
            $this->order->setDiscount($discount);
            // Run events
            $event = new GenericEvent($this->order);
            $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, $event);
            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * Removal of all items from the basket.
     */
    public function clear(): void
    {
        $this->entityManager->remove($this->order);
        $this->entityManager->flush();
    }

    /**
     * Checking if the basket is empty.
     *
     * @return bool
     */
    public function hasItems(): bool
    {
        return !$this->order->getItems()->isEmpty();
    }

    /**
     * Checking if the order has recipient.
     *
     * @return bool
     */
    public function hasRecipient(): bool
    {
        return null === $this->order->getRecipient() ? false : true;
    }

    /**
     * Checking if the order has a message to it.
     *
     * @return bool
     */
    public function hasMessage(): bool
    {
        return null === $this->order->getMessage() ? false : true;
    }

    /**
     * Checking if the order has a sender.
     *
     * @return bool
     */
    public function hasSender(): bool
    {
        return null === $this->order->getSender() ? false : true;
    }

    /**
     * Checking if the order has a delivery date and time.
     *
     * @return bool
     */
    public function hasDeliveryDate(): bool
    {
        if ($this->order->getDeliveryDate() === null || $this->order->getDeliveryInterval() === null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checking if delivery date is in the past.
     * Returns 'true' if in the past.
     *
     * @return bool
     */
    public function isDeliveryDateInPast(): bool
    {

        $date = $this->order->getDeliveryDate();
//        dd((new \DateTime('now +'. GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours')));
//        dd((new \DateTime('now +4 hours'))->diff($date)->format('%r%h'));
//        dd((new \DateTime('now +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+1 day')));

        // A '+1 day' azert kell mert az adott datum 00:00 orajat veszi.
        // Ergo, ha feb 6. reggel rendelek delutani idopontra, akkor az mar a multban van!
        // Ugyanis a delutani datum feb 6, 00:00 ora lesz adatbazisban, ami reggelhez kepest a multban van!
        $diff = (new \DateTime('now +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+1 day'));
        if ($diff->days >= 0 && $diff->invert == 0) {
            return false;
        } elseif ($diff->invert == 1) {
            return true;
        }
    }

    /**
     * Checking if the order has a payment method.
     *
     * @return bool
     */
    public function hasPayment(): bool
    {
        return null === $this->order->getPayment() ? false : true;
    }

    /**
     * Checking if the order has a shipping method.
     *
     * @return bool
     */
    public function hasShipping(): bool
    {
        return null === $this->order->getShipping() ? false : true;
    }

    /**
     * Checking if the order has a CustomerBasic or normal Customer defined.
     *
     * @return bool
     */
    public function hasCustomer(): bool
    {
        $valid = true;
        if (null === $this->storage->fetch('email') || '' === $this->storage->fetch('email')) {
            $valid = false;
        }
        if (null === $this->storage->fetch('firstname') || '' === $this->storage->fetch('firstname')) {
            $valid = false;
        }
        if (null === $this->storage->fetch('lastname') || '' === $this->storage->fetch('lastname')) {
            $valid = false;
        }
        if (null === $this->order->getBillingPhone() || '' === $this->order->getBillingPhone()) {
            $valid = false;
        }
        return true === $valid ? true : false;
    }

    /**
     * Get information needed to summarize the basket.
     *
     * @return Summary
     */
    public function summary(): Summary
    {
        return new Summary($this->order);
    }

}