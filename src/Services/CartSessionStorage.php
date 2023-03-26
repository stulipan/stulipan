<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/// !!! NINCS HASZNALATBAN !!!!
class CartSessionStorage
{
    public const SESSION_ID = 'cartId';

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

    public function set(int $cartId): void
    {
        $this->session->set(self::SESSION_ID, $cartId);
    }

    public function removeCartFromSession(): void
    {
        $this->session->remove(self::SESSION_ID);
//        $this->session->remove('email');
//        $this->session->remove('firstname');
//        $this->session->remove('lastname');
    }

    public function getCartById(): ?Cart
    {
        if ($this->has()) {
            return $this->em->getRepository(Cart::class)->find($this->get());
        }
        return null;
    }

    public function has(): bool
    {
        return $this->session->has(self::SESSION_ID);
    }

    public function get(): int
    {
        return $this->session->get(self::SESSION_ID);
    }

    /**
     * Adds data to the session.
     * Eg: email, firstname, lastname, phone - which are used in Checkout at Step1
     *
     * @param string $name
     * @param mixed  $value
     */
    public function add($name, $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     * Fetch data from the session.
     * Eg: email, firstname, lastname, phone - which are used in Checkout at Step1
     */
    public function fetch($name)
    {
        return $this->session->get($name);
    }

}