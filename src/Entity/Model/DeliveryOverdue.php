<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\Order;
use App\Entity\OrderStatus;

/**
 * !!! NEM HASZNALOM SEHOL
 */
final class DeliveryOverdue
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var boolean
     */
    private $isOverdue = false;

    /**
     * @var integer
     */
    private $days;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isDeliveryOverdue(): bool
    {
        if ($this->order->getStatus() && $this->order->getStatus()->getShortcode() === OrderStatus::ORDER_CREATED) {
            return $this->order->isDeliveryDateInPast();
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * @param int|null $days
     */
    public function setDays(?int $days): void
    {
        $this->days = $days;
    }



}