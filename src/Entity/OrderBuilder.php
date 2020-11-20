<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\Model\CustomerBasic;
use App\Model\CartGreetingCard;
use App\Event\OrderEvent;
use DateTime;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderSessionStorage;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use App\Entity\Discount;
use App\Entity\Product\Product;
use App\Model\Summary;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

use Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    private $translator;

    public function __construct(OrderSessionStorage $storage, EntityManagerInterface $entityManager,
                                EventDispatcherInterface $eventDispatcher, Security $security, TranslatorInterface $translator)
    {
        $this->storage = $storage;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;

//        $this->customer = new User();
        $this->customer = $security->getUser();
        $this->order = $this->getCurrentOrder();
        $this->translator = $translator;
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
     * @param User $customer    Add current user as Customer (to the current Order) + update email in order with email from Customer
     */
    public function setCustomer(User $customer): void
    {
        $orderBeforeId = $this->order->getId();
        $this->order->setCustomer($customer);
        $this->order->setEmail($customer->getEmail());  // must update the email

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $this->runEvent(OrderEvent::ORDER_CREATED, OrderStatus::STATUS_CREATED);
        } else {
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');
        }
    }

    /**
     * @param CustomerBasic $customerBasic    Add a CustomerBasic to the current Order
     */
    public function setCustomerBasic(CustomerBasic $customerBasic): void
    {
        $orderBeforeId = $this->order->getId();

        $this->order->setEmail($customerBasic->getEmail());
        $this->order->setFirstname($customerBasic->getFirstname());
        $this->order->setLastname($customerBasic->getLastname());

        $this->order->setBillingFirstname($customerBasic->getFirstname());
        $this->order->setBillingLastname($customerBasic->getLastname());
        $this->order->setBillingPhone($customerBasic->getPhone());
        $this->storage->add('email', $customerBasic->getEmail());
        $this->storage->add('firstname', $customerBasic->getFirstname());
        $this->storage->add('lastname', $customerBasic->getLastname());
        $this->storage->add('phone', $customerBasic->getPhone());

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $this->runEvent(OrderEvent::ORDER_CREATED, OrderStatus::STATUS_CREATED);
        } else {
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');
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
            $orderItem->setPrice($product->getPrice()->getNumericValue());

            $orderItem->setPriceTotal($orderItem->getPrice() * $orderItem->getQuantity());
            $this->order->addItem($orderItem);
        } else {
            $key = $this->indexOfProduct($product);
            $item = $this->order->getItems()->get($key);
            $quantity = $this->order->getItems()->get($key)->getQuantity() + 1;
            $price = $product->getPrice()->getNumericValue();
            $this->setItemPrice($item, $price);
            $this->setItemQuantity($item, $quantity);
        }
//        $this->order->setPriceTotal($this->order->getSummary());

//        if ($this->customer === null) {
//            $this->setCustomer($this->order->getCustomer());
//        }

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        // Run events
        if ($orderBeforeId === null) {
            $this->runEvent(OrderEvent::ORDER_CREATED, OrderStatus::STATUS_CREATED);
        } else {
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');
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
                $item->setPriceTotal($item->getQuantity()*$item->getPrice());
                $this->order->getItems()->set($key, $item);
                // Run events
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

                $this->entityManager->persist($this->order);
                $this->entityManager->flush();
            } else {
                throw new Exception($this->translator->trans('cart.not-enough-stock'));
            }
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
            $this->runEvent(OrderEvent::ORDER_CREATED, OrderStatus::STATUS_CREATED);
        } else {
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');
        }
    }

    /**
     * @param string|null $deliveryDate
     * @param null|string $deliveryInterval
     */
    public function setDeliveryDate(string $deliveryDate = null, string $deliveryInterval = null): void
    {
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
//        $this->runEvent(OrderEvent::ORDER_UPDATED, '');

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
    }

    /**
     * @param float $deliveryFee
     */
    public function setDeliveryFee(?float $deliveryFee) {
        $this->order->setDeliveryFee($deliveryFee);

        // Run events
//        $this->runEvent(OrderEvent::ORDER_UPDATED, '');

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
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
        // Run events
//        $this->runEvent(OrderEvent::ORDER_UPDATED, '');

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
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
                $this->entityManager->persist($this->order);
                $this->entityManager->flush();
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

        // Run events
//        $this->runEvent(OrderEvent::ORDER_UPDATED, '');

        $this->entityManager->persist($this->order);
        if ($prevShippingAddress) {
            $this->entityManager->remove($prevShippingAddress);
        }
        $this->entityManager->flush();
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
        // Run events
//        $this->runEvent(OrderEvent::ORDER_UPDATED, '');

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();
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
                $this->entityManager->persist($this->order);
                $this->entityManager->flush();
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
        $this->order->setBillingFirstname($this->storage->fetch('firstname'));
        $this->order->setBillingLastname($this->storage->fetch('lastname'));
        $this->order->setBillingCompany(null);
        $this->order->setBillingVatNumber(null);
        $this->order->setBillingPhone($this->storage->fetch('phone'));
        $this->order->setBillingAddress(null);

        // Run events
//        $this->runEvent(OrderEvent::ORDER_UPDATED, '');

        $this->entityManager->persist($this->order);

        if ($prevBillingAddress) {
            $this->entityManager->remove($prevBillingAddress);
        }
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
            $item->setPrice($price);
            $this->order->getItems()->set($key, $item);
            // Run events
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

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
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

//    /**
//     * Update the price for an existing product.
//     * A product may have several prices because of its subproducts.
//     *
//     * @param OrderItem $item
//     * @param string $name
//     * @throws Exception
//     */
//    public function setItemAttribute(OrderItem $item, string $name): void
//    {
//        if ($this->order && $this->order->getItems()->contains($item)) {
//            $key = $this->order->getItems()->indexOf($item);
//            $this->order->getItems()->set($key, $item);
//            // Run events
////            $this->runEvent(OrderEvent::ORDER_UPDATED, '');
//
//            $this->entityManager->persist($this->order);
//            $this->entityManager->flush();
//        }
//    }

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
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

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
     * @param CartGreetingCard $greetingCard
     */
    public function setMessage(?CartGreetingCard $greetingCard): void
    {
        if ($this->order) {
            if ($greetingCard) {
                $this->order->setMessage($greetingCard->getMessage());
                $this->order->setMessageAuthor($greetingCard->getAuthor());
                // Run events
//                $this->runEvent(OrderEvent::ORDER_UPDATED, '');

                $this->entityManager->persist($this->order);
                $this->entityManager->flush();
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

            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
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
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
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
            // Run events
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
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
            // Run events
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

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
            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

            $this->entityManager->persist($this->order);
            $this->entityManager->flush();
        }
    }

    /**
     * @param ClientDetails|null $clientDetails
     */
    public function setClientDetails(?ClientDetails $clientDetails): void
    {
        if ($this->order) {
            $this->order->setClientDetails($clientDetails);
            // Run events
//            $this->runEvent(OrderEvent::ORDER_UPDATED, '');

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

//    /**
//     * Checking if delivery date is in the past.
//     * Returns 'true' if in the past.
//     *
//     * @return bool
//     */
//    public function isDeliveryDateInPast(): bool
//    {
//        $date = $this->order->getDeliveryDate();
////        dd((new \DateTime('now +'. GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours')));
////        dd((new \DateTime('now +4 hours'))->diff($date)->format('%r%h'));
////        dd((new \DateTime('now +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+1 day')));
//
//        if ($date) {
//            /** A '+1 day' azert kell mert az adott datum 00:00 orajat veszi.
//             * Ergo, ha feb 6. reggel rendelek delutani idopontra, akkor az mar a multban van!
//             * Ugyanis a delutani datum feb 6, 00:00 ora lesz adatbazisban, ami reggelhez kepest a multban van!
//             */
//            $diff = (new DateTime('today +' . GeneralUtils::DELIVERY_DATE_HOUR_OFFSET . ' hours'))->diff($date->modify('+0 day'));
//            if ($diff->days >= 0 && $diff->invert == 0) {
//                return false;
//            } elseif ($diff->invert == 1) {
//                return true;
//            }
//        }
//        return true;
//    }

    /**
     * Checking if the order has a payment method.
     *
     * @return bool
     */
    public function hasPaymentMethod(): bool
    {
        return null === $this->order->getPaymentMethod() ? false : true;
    }

    /**
     * Checking if the order has a shipping method.
     *
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
        $valid = true;
        if ($this->customer) {
            // We check only for Phone because 'email', 'firstname' and 'lastname' are mandatory for a User/Customer
            if (!$this->customer->getPhone()) {
                $valid = false;
            }
        } else {
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

    private function runEvent($event, $orderStatus = null) {
        $channel = OrderLog::CHANNEL_CHECKOUT;

        $eventName = new OrderEvent($this->order, [
            'channel' => $channel,
            'orderStatus' => $orderStatus,
        ]);
        $this->eventDispatcher->dispatch($event, $eventName);
    }

}