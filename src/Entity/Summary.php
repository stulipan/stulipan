<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Order;

final class Summary
{
    /**
     * @var Order
     */
    private $order;

//    /**
//     * @var float
//     */
//    private totalAmountToPay = 0;

    /**
     * Summary constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Return the total/final amount to pay in the cart
     *
     * @return float
     */
    public function getTotalAmountToPay(): float
    {
        $totalToPay = 0;
        $totalItems = count($this->order->getItems());
        foreach ($this->order->getItems() as $item) {
            $totalToPay += $item->getPrice() * $item->getQuantity();
        }

        $totalToPay += $this->order->getDeliveryFee();
        return $totalToPay;
    }

    /**
     * Returns the value of the basket before applying the discount.
     *
     * @return float
     */
    public function getTotalBeforeDiscount(): float
    {
        $price = 0;
        foreach ($this->order->getItems() as $item) {
            $price += $item->getPrice() * $item->getQuantity();
        }
        return $price;
    }



//    /**
//     * Return
//     *
//     * @return float
//     */
//    public function getItemsPriceTotal(): float
//    {
//        return $this->order->getItemsPriceTotal();
//    }
//
//
//    /**
//     * Return discount price
//     *
//     * @return float
//     */
//    public function getDiscount(): float
//    {
//        $discount = 0;
//        if ($this->order->getDiscount()) {
//            $discount = $this->getPriceItemsBeforeDiscount() * $this->order->getDiscount()->getDiscount() / 100;
//        }
//        return $discount;
//    }
//
}