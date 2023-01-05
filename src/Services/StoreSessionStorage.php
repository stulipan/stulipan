<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Cart;
use App\Entity\Checkout;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StoreSessionStorage
{
    public const CART_ID = 'cartId';
    public const CHECKOUT_ID = 'checkoutId';
    public const ORDER_ID = 'orderId';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function getCartById(): ?Cart
    {
        if ($this->has(self::CART_ID)) {
            return $this->em->getRepository(Cart::class)->find($this->get(self::CART_ID));
        }
        return null;
    }

    public function getCheckoutById(): ?Checkout
    {
        if ($this->has(self::CHECKOUT_ID)) {
            return $this->em->getRepository(Checkout::class)->find($this->get(self::CHECKOUT_ID));
        }
        return null;
    }

    public function getOrderById(): ?Order
    {
        if ($this->has(self::ORDER_ID)) {
            return $this->em->getRepository(Order::class)->find($this->get(self::ORDER_ID));
        }
        return null;
    }

//    public function set(int $orderId): void
//    {
//        $this->session->set(self::ORDER_ID, $orderId);
//    }

    public function set($attributeName, $value): void
    {
        $this->session->set($attributeName, $value);
    }

    public function removeOrderFromSession(): void
    {
        $this->session->remove(self::ORDER_ID);
        $this->session->remove('email');
        $this->session->remove('firstname');
        $this->session->remove('lastname');
    }

    public function has(string $attributeName): bool
    {
        return $this->session->has($attributeName);
    }

    public function get(string $attributeName)
    {
        return $this->session->get($attributeName);
    }

    public function remove(string $attributeName)
    {
        return $this->session->remove($attributeName);
    }

    
    // Ezekre most mar NINCS szukseg:

    /**
     * Adds data (attribute) to the session.
     * Eg: email, firstname, lastname, phone - which are used in Checkout at Step1
     *
     * @param string $attributeName
     * @param mixed  $value
     */
    public function add($attributeName, $value): void
    {
        $this->session->set($attributeName, $value);
    }

    /**
     * Fetch an attribute from the session.
     * Eg: email, firstname, lastname, phone - which are used in Checkout at Step1
     */
    public function fetch($attributeName)
    {
        return $this->session->get($attributeName);
    }

}