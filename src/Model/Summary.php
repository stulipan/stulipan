<?php

declare(strict_types=1);

namespace App\Model;

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
     * Returns the total/final amount to pay in the cart
     * Returns the total price for the items + delivery fee
     *
     * @return float
     */
    public function getTotalAmountToPay(): float
    {
        return $this->getPriceTotal() + $this->order->getDeliveryFee();
    }

    /**
     * Sums all items in cart. 
     * Returns the total price in cart. 
     *
     * @return float
     */
    public function getPriceTotal(): float
    {
        $priceTotal = 0;
        $totalItems = count($this->order->getItems());
        foreach ($this->order->getItems() as $item) {
            $priceTotal += $item->getPriceTotal();
            // The above is equivalent to this:
            // $priceTotal += $item->getPrice() * $item->getQuantity();
        }
        return $priceTotal;
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
            $price += $item->getUnitPrice() * $item->getQuantity();
        }
        return $price;
    }


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