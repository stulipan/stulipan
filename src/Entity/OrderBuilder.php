<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Security\Core\Security;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderSessionStorage;
use App\Entity\Payment;
use App\Entity\Shipping;
use App\Entity\Discount;
use App\Entity\Product;
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
     * @var Security
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
    
    public function __construct(OrderSessionStorage $storage, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $user)
    {

        $this->storage = $storage;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->customer = $user->getUser();
        $this->order = $this->getCurrentOrder();
//        dump($this->getCurrentOrder());
//        $this->order->set($this->getCurrentOrder());

    }

    public function getId(): int
    {

        return $this->order->getId();
    }

    /**
     *
     */
    public function getCustomer()
    {
        return $this->customer->getId();
    }
        /**
     * @return Order
     */
    public function getCurrentOrder(): Order
    {
        $order = $this->storage->getOrderById();
        if ($order !== null) {
            return $order;
        }

        $newOrder = new Order;
        $newOrder->setCustomer($this->customer);
//        $newOrder->setPayment(1);
//        $newOrder->setShipping(2);

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
            $orderItem->setPrice($product->getGrossPrice());
            $orderItem->setPriceTotal($product->getGrossPrice() * $orderItem->getQuantity());

            $this->order->addItem($orderItem);
        } else {
            $key = $this->indexOfProduct($product);
            $item = $this->order->getItems()->get($key);
            $quantity = $this->order->getItems()->get($key)->getQuantity() + 1;
            $this->setItemQuantity($item, $quantity);
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
    public function items(): Collection
    {
        return $this->order->getItems();
    }

    /**
     * Set message method
     *
     * @param string $message
     * @param string $messageAuthor
     */
    public function setMessageAndAuthor(string $message, string $messageAuthor): void
    {
        if ($this->order) {
            $this->order->setMessage($message);
            $this->order->setMessageAuthor($messageAuthor);
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
    public function isEmpty(): bool
    {
        return !$this->order->getItems();
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