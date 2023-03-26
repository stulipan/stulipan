<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\OrderLog;
use App\Event\StoreEvent;
use App\Model\CartGreetingCard;
use App\Entity\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class CartBuilder
{
    /**
     * @var StoreSessionStorage
     */
    private $storage;

    /**
     * @var Cart
     */
    private $cart;

    private $em;
    private $eventDispatcher;
    private $translator;

    public function __construct(StoreSessionStorage $storage, EntityManagerInterface $entityManager,
                                EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        $this->storage = $storage;
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->cart = $this->getCurrent();
    }
    /**
     * @return Cart
     */
    public function getCurrent(): Cart
    {
        /** Returns the Cart which is in the session, if any */
        $cart = $this->storage->getCartById();
        if ($cart !== null) {
            return $cart;
        }

        /** Creates a new Cart (with id!) if there's none in the session */
        $newCart = new Cart;
        return $newCart;
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
        $cartBeforeId = $this->cart->getId();
        if (!$this->containsTheProduct($product)) {
            $item = new CartItem();
            $item->setCart($this->cart);
            $item->setProduct($product);

            if ($product->hasEnoughStock($quantity)) {
                $item->setQuantity($quantity);
            } else {
                throw new Exception($this->translator->trans('cart.product.not-enough-stock'));
            }
            $this->cart->addItem($item);
        } else {
            $key = $this->indexOfProduct($product);
            $item = $this->cart->getItems()->get($key);
            $quantity += $item->getQuantity();

            if ($product->hasEnoughStock($quantity)) {
                $item->setQuantity($quantity);
            } else {
                throw new Exception($this->translator->trans('cart.product.not-enough-stock'));
            }
        }

        $this->em->persist($this->cart);
        $this->em->flush();

        // Run events
        if ($cartBeforeId === null) {
            $this->runEvent(StoreEvent::CART_CREATE);
        } else {
            $this->runEvent(StoreEvent::CART_UPDATE);
        }
    }

    /**
     * Update the quantity for an existing product.
     *
     * @param CartItem $item
     * @param integer $quantity
     * @throws Exception
     */
    public function setItemQuantity(CartItem $item, int $quantity): void
    {
        if ($this->cart && $this->cart->getItems()->contains($item)) {
            if ($item->getProduct()->hasEnoughStock($quantity)) {
                $key = $this->cart->getItems()->indexOf($item);
                $item->setQuantity($quantity);
                $this->cart->getItems()->set($key, $item);

                $this->em->persist($this->cart);
                $this->em->flush();

                // Run events
                $this->runEvent(StoreEvent::CART_UPDATE);
            } else {
                throw new Exception($this->translator->trans('cart.product.not-enough-stock'));
            }
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
        foreach ($this->cart->getItems() as $item) {
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
        foreach ($this->cart->getItems() AS $key => $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Removing the product from the basket.
     *
     * @param CartItem $item
     * @throws Exception
     */
    public function removeItem(CartItem $item): void
    {
        if ($this->cart && $this->cart->getItems()->contains($item)) {
            $this->cart->removeItem($item);

            $this->em->persist($this->cart);
            $this->em->flush();

            // Run events
            $this->runEvent(StoreEvent::CART_UPDATE);
        }
    }

    /**
     * Set message method
     *
     * @param CartGreetingCard $greetingCard
     */
    public function setMessage(?CartGreetingCard $greetingCard): void
    {
        if ($this->cart) {
            if ($greetingCard) {
                $this->cart->setMessage($greetingCard->getMessage());
                $this->cart->setMessageAuthor($greetingCard->getAuthor());

                $this->em->persist($this->cart);
                $this->em->flush();
            }
        }
    }

    private function runEvent($eventName) {
        $channel = OrderLog::CHANNEL_CHECKOUT;

        $event = new StoreEvent(
            $this->cart,
            [
                'channel' => $channel,
//                'status' => $status,
            ]
        );
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}