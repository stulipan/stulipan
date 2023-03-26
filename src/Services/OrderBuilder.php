<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Address;
use App\Entity\Checkout;
use App\Entity\OrderAddress;
use App\Entity\OrderLog;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Event\StoreEvent;
use DateTime;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product\Product;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderBuilder
{
    /**
     * @var StoreSessionStorage
     */
    private $storage;

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

    public function __construct(StoreSessionStorage      $storage, EntityManagerInterface $entityManager,
                                EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        $this->storage = $storage;
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;

        $this->order = $this->getCurrentOrder();
    }

    /**
     * @return Order
     */
    public function getCurrentOrder(): Order
    {
        // Returns the Order which is in the session, if any
        $order = $this->storage->getOrderById();
        if ($order !== null) {
            return $order;
        }

        // Creates a new Order (with id!) if there's none in the session
        $newOrder = new Order;
        return $newOrder;
    }

    public function initializeOrder()
    {
        $orderBeforeId = $this->order->getId();
        if ($orderBeforeId === null) {
            $this->runEvent(StoreEvent::IMPORT_ITEMS_FROM_CHECKOUT);

            $this->importDataFromCheckout();
            $this->em->persist($this->order);
            $this->em->flush();

            // Run events
            $this->runEvent(StoreEvent::ORDER_CREATE);
        } else {
            // Note: When Cart items are updated, Order is updated automatically through 'StoreEvent::CART_UPDATE' event
            $this->importDataFromCheckout();
            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    public function importDataFromCheckout()
    {
        $checkout = $this->storage->getCheckoutById();
        $checkout->setOrder($this->order);
        $this->em->persist($checkout);      // !!!

        $this->order->setMessage($checkout->getMessage());
        $this->order->setMessageAuthor($checkout->getMessageAuthor());

        $this->order->setCustomer($checkout->getCustomer());
        $this->order->setEmail($checkout->getEmail());
        $this->order->setPhone($checkout->getCustomer()->getPhone());
        $this->order->setFirstname($checkout->getCustomer()->getFirstname());
        $this->order->setLastname($checkout->getCustomer()->getLastname());

        $shippingAddress = $this->createOrderAddress($checkout->getRecipient()->getAddress(), Address::DELIVERY_ADDRESS);

        $this->order->setShippingFirstname($checkout->getRecipient()->getFirstname());
        $this->order->setShippingLastname($checkout->getRecipient()->getLastname());
        $this->order->setShippingPhone($checkout->getRecipient()->getPhone());
        $this->order->setShippingAddress($shippingAddress);

        if ($checkout->isSameAsShipping()) {
            $data = $checkout->getRecipient();
        } else {
            $data = $checkout->getSender();
        }
        $billingAddress = $this->createOrderAddress($data->getAddress(), Address::BILLING_ADDRESS);

        $this->order->setBillingFirstname($data->getFirstname());
        $this->order->setBillingLastname($data->getLastname());
        $this->order->setBillingPhone($data->getPhone());
        $this->order->setBillingAddress($billingAddress);

        if (!$checkout->isSameAsShipping()) {
            $this->order->setBillingCompany($checkout->getSender()->getCompany());
            $this->order->setBillingVatNumber($checkout->getSender()->getCompanyVatNumber());
        }

        $this->order->setShippingMethod($checkout->getShippingMethod());
        $this->order->setPaymentMethod($checkout->getPaymentMethod());
        $this->order->setShippingFee($checkout->getShippingFee());
        $this->order->setPaymentFee($checkout->getPaymentFee());
        $this->order->setSchedulingPrice($checkout->getSchedulingPrice());

        $this->order->setDeliveryDate($checkout->getDeliveryDate());
        $this->order->setDeliveryInterval($checkout->getDeliveryInterval());

        $this->order->setIsAcceptedTerms($checkout->isAcceptedTerms());
    }

    private function importItems(Checkout $checkout, Order &$order)  // $checkout is passed by reference
    {
        foreach ($checkout->getItems() as $checkoutItem) {
            $product = $checkoutItem->getProduct();
            if (!$this->containsTheProduct($product)) {
                $item = new OrderItem();
                $item->setOrder($order);
                $item->setProduct($checkoutItem->getProduct());

                if ($checkoutItem->getProduct()->hasEnoughStock($checkoutItem->getQuantity())) {
                    $item->setQuantity($checkoutItem->getQuantity());
                    $item->setUnitPrice($checkoutItem->getProduct()->getSellingPrice());
                } else {
                    throw new Exception($this->translator->trans('cart.product.not-enough-stock'));
                }
                $order->addItem($item);
            }
            else {
                $key = $this->indexOfProduct($product);
                $item = $this->order->getItems()->get($key);
                $newQuantity = $checkoutItem->getQuantity();

                if ($product->hasEnoughStock($newQuantity)) {
                    $item->setQuantity($newQuantity);
                } else {
                    throw new Exception($this->translator->trans('cart.product.not-enough-stock'));
                }
            }
        }
    }

    /**
     * Checking if the Order contains the product.
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
            if ($item->getProduct()->getId() === $product->getId()) {
                return $key;
            }
        }
        return null;
    }

    private function createOrderAddress(Address $address, int $type)
    {
        $orderAddress = new OrderAddress();
        $orderAddress->setAddressType($type);
        $orderAddress->setStreet($address->getStreet());
        $orderAddress->setCity($address->getCity());
        $orderAddress->setZip($address->getZip());
        $orderAddress->setProvince($address->getProvince());
        $orderAddress->setCountry($address->getCountry());

        return $orderAddress;
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
            $this->em->persist($this->order);
            $this->em->flush();
        }
    }

    /**
     * @return StoreSessionStorage
     */
    public function getCurrentSession(): StoreSessionStorage
    {
        return $this->storage;
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
     * Set order status.
     * This is being deprecated from March 2022. Status should be calculated:
     *      -- if placed, from postedAt
     *      -- if other status, from Payment and Fulfillment.
     *
     * @param OrderStatus $status
     */
    public function setStatus(OrderStatus $status): void
    {
        if ($this->order) {
            $this->order->setStatus($status);

//            // Run events
//            $this->runEvent(StoreEvent::ORDER_UPDATE, $status->getShortcode());

            $this->em->persist($this->order);
            $this->em->flush();
        }
    }


//    /**
//     * @param ClientDetails|null $clientDetails
//     */
//    public function setClientDetails(?ClientDetails $clientDetails): void
//    {
//        if ($this->order) {
//            $this->order->setClientDetails($clientDetails);
//
//            $this->em->persist($this->order);
//            $this->em->flush();
//        }
//    }

    /**
     * @param string|null $token
     */
    public function setPostedAt(): void
    {
        $this->order->setPostedAt(new DateTime('now'));
        $this->em->persist($this->order);
        $this->em->flush();
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



    private function runEvent(string $eventName, array $params = []) {
        $params['channel'] = OrderLog::CHANNEL_CHECKOUT;

        $event = new StoreEvent($this->order, $params);
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}