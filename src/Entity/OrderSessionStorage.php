<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Order;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderSessionStorage
{
    private const ORDER_KEY_NAME = 'orderId';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function set(int $orderId): void
    {
        $this->session->set(self::ORDER_KEY_NAME, $orderId);
    }

    public function removeOrderFromSession(): void
    {
        $this->session->remove(self::ORDER_KEY_NAME);
        $this->session->remove('email');
        $this->session->remove('firstname');
        $this->session->remove('lastname');
    }

    public function getOrderById(): ?Order
    {
        if ($this->has()) {
            return $this->entityManager->getRepository(Order::class)->findOneById($this->get());
        }
        return null;
    }

    public function has(): bool
    {
        return $this->session->has(self::ORDER_KEY_NAME);
    }

    public function get(): int
    {
        return $this->session->get(self::ORDER_KEY_NAME);
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