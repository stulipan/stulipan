<?php

declare(strict_types=1);

namespace App\Services;

use App\Controller\Utils\GeneralUtils;
use App\Entity\ClientDetails;
use App\Entity\Customer;
use App\Entity\OrderAddress;
use App\Entity\OrderLog;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Model\CartGreetingCard;
use App\Event\OrderEvent;
use App\Services\AbandonedOrderRetriever;
use DateTime;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use App\Entity\Discount;
use App\Entity\Product\Product;
use App\Model\Summary;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderBuilder
{
    /**
     * @var OrderSessionStorage
     */
    private $storage;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $translator;

    public function __construct(OrderSessionStorage $storage, EntityManagerInterface $entityManager,
                                EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        $this->storage = $storage;
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;

        $this->order = $this->getCurrentOrder();
        $this->customer = $this->order->getCustomer();
    }

    public function getId(): ?int
    {
        return $this->order->getId();
    }

    public function getCustomer()
    {
        return $this->order->getCustomer();
    }

    /**
     * @param Customer $customer    Add current user as Customer (to the current Order) + update email in order with email from Customer
     */
    public function setCustomer(Customer $customer): void
    {
        $orderBeforeId = $this->order->getId();

        if ($orderBeforeId) {
            $this->order = $this->getCurrentOrder();
            $customer->addOrder($this->order);
            $this->order->setCustomer($customer);
            $this->order->setEmail($customer->getEmail());
            $this->order->setPhone($customer->getPhone());
            $this->order->setFirstname($customer->getFirstname());
            $this->order->setLastname($customer->getLastname());

//            // prepare billing info upfront
//            $this->order->setBillingFirstname($customer->getFirstname());
//            $this->order->setBillingLastname($customer->getLastname());
//            $this->order->setBillingPhone($customer->getPhone());

            $this->em->persist($this->order);
            $this->em->flush();
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
        /** Returns the Order which is in the session, if any */
        $order = $this->storage->getOrderById();
        if ($order !== null) {
            return $order;
        }

        /** Creates a new Order (with id!) if there's none in the session */
        $newOrder = new Order;
        return $newOrder;
    }

    /**
     * @param Order $order
     */
    public function setCurrentOrder(Order $order)
    {
        $this->storage->set($order->getId());
        $this->order = $order;
    }

    /**
     * Adds a new item (product) to the basket.
     * If the product already in the cart, then increases its quantity.
     *
     * @param Product $product
     * @param int $quantity
     * @throws Exception
     */
    public function addItem(Product $product, int $quantity): void
    {
        $orderBeforeId = $this->order->getId();
        if (!$this->containsTheProduct($product)) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($this->order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setUnitPrice($product->getPrice()->getNumericValue());

            $orderItem->setPriceTotal($orderItem->getUnitPrice() * $orderItem->getQuantity());
            $this->order->addItem($orderItem);
        } else {
            $key = $this->indexOfProduct($product);
            $item = $this->order->getItems()->get($key);
            $quantity = $this->order->getItems()->get($key)->getQuantity() + 1;
            $price = $product->getPrice()->getNumericValue();
            $this->setItemPrice($item, $price);
            $this->setItemQuantity($item, $quantity);
        }

        $this->em->persist($this->order);
        $this->em->flush();

        // Run events
        if ($orderBeforeId === null) {
            $this->runEvent(OrderEvent::PRODUCT_ADDED_TO_CART, OrderStatus::CART_CREATED);
        } else {
//            $this->runEvent(OrderEvent::PRODUCT_ADDED_TO_CART, OrderStatus::CART_UPDATED);
        }
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
            if ($item->getProduct()->hasEnoughStock($quantity)) {
                $key = $this->order->getItems()->indexOf($item);
                $item->setQuantity($quantity);
                $item->setPriceTotal($item->getQuantity() * $item->getUnitPrice());
                $this->order->getItems()->set($key, $item);

                $this->em->persist($this->order);
                $this->em->flush();
            } else {
                throw new Exception($this->translator->trans('cart.product.not-enough-stock'));
            }
        }
    }

//    /**
//     * Adding an Item to the basket from a previous Order, which hasn't been placed (abandoned cart scenario).
//     * Adds only Items which aren't already present in the current Order.
//     *
//     * !! Átrakom az Itemeket a régi Orderből az újba. A régibe nem maradnak meg !!
//     * Ez később baj lehet, amikor elhagyott kosár statisztikát csinálnánk
//     *
//     *
//     * @param OrderItem $item
//     * @return void
//     */
//    public function addItemFromPreviousOrder(OrderItem $orderItem): void
//    {
//        $orderBeforeId = $this->order->getId();
//        if (!$this->containsTheProduct($orderItem->getProduct())) {
//            $orderItem->setOrder($this->order);
//            $this->order->addItem($orderItem);
//        }
//        $this->em->persist($this->order);
//        $this->em->flush();
//
//        // Run events
//        if ($orderBeforeId === null) {
//            $this->runEvent(OrderEvent::ORDER_CREATED, OrderStatus::ORDER_CREATED);
//        } else {
////            $this->runEvent(OrderEvent::ORDER_UPDATED, '');
//        }
//    }

    /**
     * @param string|null $deliveryDate
     * @param null|string $deliveryInterval
     */
    public function setDeliveryDate(string $deliveryDate = null, string $deliveryInterval = null): void
    {
        $isPersisting = false;
        if ($deliveryDate) {
            $deliveryDate = DateTime::createFromFormat('!Y-m-d', $deliveryDate);
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
                $isPersisting = true;
            }
        }
        if ($deliveryInterval) {
            if ($this->order->getDeliveryInterval() && $this->order->getDeliveryInterval() === $deliveryInterval) {
                // do nothing
            } else {
                $this->order->setDeliveryInterval($deliveryInterval);
                $isPersisting = true;
            }
        }

        if ($isPersisting) {
            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * @param float $deliveryFee
     */
    public function setDeliveryFee(?float $deliveryFee) {
        $this->order->setDeliveryFee($deliveryFee);

        $this->em->persist($this->order);
        $this->em->flush();
    }

    /**
     * @param Recipient $recipient
     */
    public function setRecipient(Recipient $recipient): void
    {
        $this->order->setRecipient($recipient);
        $this->order->setShippingFirstname($recipient->getFirstname());
        $this->order->setShippingLastname($recipient->getLastname());
        $this->order->setShippingPhone($recipient->getPhone());

        $shippingAddress = new OrderAddress();
        $shippingAddress->setStreet($recipient->getAddress()->getStreet());
        $shippingAddress->setCity($recipient->getAddress()->getCity());
        $shippingAddress->setZip($recipient->getAddress()->getZip());
        $shippingAddress->setProvince($recipient->getAddress()->getProvince());
        $shippingAddress->setCountry($recipient->getAddress()->getCountry());
        $shippingAddress->setAddressType($recipient->getAddress()->getAddressType());

        $this->order->setShippingAddress($shippingAddress);

        $this->em->persist($this->order);
        $this->em->flush();
    }

    public function setFallbackRecipient(): void
    {
        $customer = $this->order->getCustomer();
        if ($customer) {
            if ($customer->hasRecipients()) {
                if ($customer->getLastOrder()) {
                    $recipient = $customer->getLastOrder()->getRecipient();
                } else {
                    $recipient = $customer->getRecipients()->last();
                }
                $this->order->setRecipient($recipient);
                $this->em->persist($this->order);
                $this->em->flush();
            }
        }
        return;
    }

    /**
     * Remove Recipient from Order
     */
    public function removeRecipient(): void
    {
        $prevShippingAddress = $this->order->getShippingAddress();
        $this->order->setRecipient(null);
        $this->order->setShippingFirstname(null);
        $this->order->setShippingLastname(null);
        $this->order->setShippingPhone(null);
        $this->order->setShippingAddress(null);

        $this->em->persist($this->order);
        if ($prevShippingAddress) {
            $this->em->remove($prevShippingAddress);
        }
        $this->em->flush();
    }

    /**
     * @param Sender $sender
     */
    public function setSender(Sender $sender): void
    {
        $this->order->setSender($sender);
        $this->order->setBillingFirstname($sender->getFirstname());
        $this->order->setBillingLastname($sender->getLastname());
        $this->order->setBillingCompany($sender->getCompany());
        $this->order->setBillingVatNumber($sender->getCompanyVatNumber());
//        $this->order->setBillingPhone($sender->getPhone());

        $billingAddress = new OrderAddress();
        $billingAddress->setStreet($sender->getAddress()->getStreet());
        $billingAddress->setCity($sender->getAddress()->getCity());
        $billingAddress->setZip($sender->getAddress()->getZip());
        $billingAddress->setProvince($sender->getAddress()->getProvince());
        $billingAddress->setCountry($sender->getAddress()->getCountry());
        $billingAddress->setAddressType($sender->getAddress()->getAddressType());

        $this->order->setBillingAddress($billingAddress);

        $this->em->persist($this->order);
        $this->em->flush();
    }

    public function setFallbackSender(): void
    {
        $customer = $this->order->getCustomer();
        if ($customer) {
            if ($customer->hasSenders()) {
                if ($customer->getLastOrder()) {
                    $sender = $customer->getLastOrder()->getSender();
                } else {
                    $sender = $customer->getSenders()->last();
                }
                $this->order->setSender($sender);
                $this->em->persist($this->order);
                $this->em->flush();
            }
        }
        return;
    }

    /**
     * Remove Sender from Order
     */
    public function removeSender(): void
    {
        $prevBillingAddress = $this->order->getBillingAddress();
        $this->order->setSender(null);
        $this->order->setBillingFirstname($this->customer->getFirstname());
        $this->order->setBillingLastname($this->customer->getLastname());
        $this->order->setBillingCompany(null);
        $this->order->setBillingVatNumber(null);
        $this->order->setBillingPhone($this->customer->getPhone());
        $this->order->setBillingAddress(null);

        $this->em->persist($this->order);

        if ($prevBillingAddress) {
            $this->em->remove($prevBillingAddress);
        }
        $this->em->flush();
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

//    /**
//     * Counts the number of items in an order
//     *
//     * @return int
//     */
////    public function countItems(): int
//    public function itemCount(): int
//    {
////        $count = [];
//        $c = 0;
//        foreach ($this->order->getItems() as $item) {
//            if ($item->getId()) {
//                $c += 1;
//            }
////            $count[$item->getId()] = ($count[$item->getId()] ?? 0)+ 1;
//        }
////        return count($count);
//        return $c;
//    }

    /**
     * Update the price for an existing product.
     *
     * @param OrderItem $item
     * @param integer $price
     * @throws Exception
     */
    public function setItemPrice(OrderItem $item, float $price): void
    {
        if ($this->order && $this->order->getItems()->contains($item)) {
            $key = $this->order->getItems()->indexOf($item);
            $item->setUnitPrice($price);
            $this->order->getItems()->set($key, $item);

            $this->em->persist($this->order);
            $this->em->flush();
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

            $this->em->persist($this->order);
            $this->em->flush();
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

            $this->em->persist($this->order);
            $this->em->flush();
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
     * @param CartGreetingCard $greetingCard
     */
    public function setMessage(?CartGreetingCard $greetingCard): void
    {
        if ($this->order) {
            if ($greetingCard) {
                $this->order->setMessage($greetingCard->getMessage());
                $this->order->setMessageAuthor($greetingCard->getAuthor());

                $this->em->persist($this->order);
                $this->em->flush();
            }
        }
    }

    /**
     * Set order status
     *
     * @param OrderStatus $status
     */
    public function setStatus(OrderStatus $status): void
    {
        if ($this->order) {
            $this->order->setStatus($status);

            // Run events
            $this->runEvent(OrderEvent::ORDER_UPDATED, $status->getShortcode());

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * Set payment status
     *
     * @param PaymentStatus $paymentStatus
     */
    public function setPaymentStatus(PaymentStatus $paymentStatus): void
    {
        if ($this->order) {
            $this->order->setPaymentStatus($paymentStatus);

            // Run events
            $this->runEvent(OrderEvent::PAYMENT_UPDATED, $paymentStatus->getShortcode());

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }


    /**
     * Set payment method
     *
     * @param PaymentMethod $payment
     */
    public function setPaymentMethod(PaymentMethod $payment): void
    {
        if ($this->order) {
            $this->order->setPaymentMethod($payment);

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * Set shipping method
     *
     * @param ShippingMethod $shippingMethod
     */
    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        if ($this->order) {
            $this->order->setShippingMethod($shippingMethod);

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * @param bool|null $isAcceptedTerms
     */
    public function setIsAcceptedTerms(?bool $isAcceptedTerms): void
    {
        if ($this->order) {
            $this->order->setIsAcceptedTerms($isAcceptedTerms);

            $this->em->persist($this->order);
            $this->em->flush();
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

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * @param ClientDetails|null $clientDetails
     */
    public function setClientDetails(?ClientDetails $clientDetails): void
    {
        if ($this->order) {
            $this->order->setClientDetails($clientDetails);

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->order->setToken($token);

        $this->em->persist($this->order);
        $this->em->flush();
    }

    /**
     * Removal of all items from the basket.
     */
    public function clear(): void
    {
        $this->em->remove($this->order);
        $this->em->flush();
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
     * @return bool
     */
    public function hasPaymentMethod(): bool
    {
        return null === $this->order->getPaymentMethod() ? false : true;
    }

    /**
     * @return bool
     */
    public function hasShippingMethod(): bool
    {
        return null === $this->order->getShippingMethod() ? false : true;
    }

    /**
     * Checking if the order has a CustomerBasic or normal Customer defined.
     *
     * @return bool
     */
    public function hasCustomer(): bool
    {
        if ($this->customer) {
            return true;
        }
        return false;
//        $valid = true;
//        if ($this->customer) {
//            // We check only for Phone because 'email', 'firstname' and 'lastname' are mandatory for a User/Customer
//            if (!$this->customer->getPhone()) {
//                $valid = false;
//            }
//        } else {
//            if (null === $this->storage->fetch('email') || '' === $this->storage->fetch('email')) {
//                $valid = false;
//            }
//            if (null === $this->storage->fetch('firstname') || '' === $this->storage->fetch('firstname')) {
//                $valid = false;
//            }
//            if (null === $this->storage->fetch('lastname') || '' === $this->storage->fetch('lastname')) {
//                $valid = false;
//            }
//            if (null === $this->order->getBillingPhone() || '' === $this->order->getBillingPhone()) {
//                $valid = false;
//            }
//        }
//        return true === $valid ? true : false;
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

    private function runEvent($eventName, $status = null) {
        $channel = OrderLog::CHANNEL_CHECKOUT;

        $event = new OrderEvent(
            $this->order,
            [
                'channel' => $channel,
                'status' => $status,
            ]
        );
        $this->eventDispatcher->dispatch($event, $eventName);
    }

}