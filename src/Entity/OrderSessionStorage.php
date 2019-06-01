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

    public function remove(): void
    {
        $this->session->remove(self::ORDER_KEY_NAME);
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
     * Adds an attribute.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function add($name, $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     *
     */
    public function fetch($name)
    {
        return $this->session->get($name);
    }

}