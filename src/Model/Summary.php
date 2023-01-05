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

    /**
     * Summary constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

//    /**
//     * Returns the total/final amount to pay in the cart
//     * Returns the total price for the items + delivery fee
//     *
//     * @return float
//     */
//    public function getTotalAmountToPay(): float
//    {
//        return $this->getPriceTotal() + $this->order->getShippingFeeToPay() + $this->order->getPaymentFeeToPay();
////        return $this->getTotalAfterSale() + $this->order->getShippingFeeToPay() + $this->order->getPaymentFeeToPay();
//
//    }

//    /**
//     * Sums all items in cart.
//     * @return float
//     */
//    public function getItemsPrice(): float
//    {
//        $total = 0;
//        foreach ($this->order->getItems() as $item) {
//            $total += $item->getPriceTotal();
//            // The above is equivalent to this: $priceTotal += $item->getPrice() * $item->getQuantity();
//        }
//        return $total;
//    }
//
//    /**
//     * DEPREKALTAM !!!!!!!
//     */
//    public function getPriceTotal(): float
//    {
//        return $this->getItemsPrice();
//    }
//
//    public function getAmountPaidByCustomer(): float
//    {
//        if ($this->order->getPaymentStatus()->getShortcode() === PaymentStatus::STATUS_PAID) {
//            return $this->getTotalAmountToPay();
//        }
//        return 0;
//    }

//    /**
//     * Returns the value of the basket before applying the discount.
//     *
//     * @return float
//     */
//    public function getTotalBeforeDiscount(): float
//    {
//        $price = 0;
//        foreach ($this->order->getItems() as $item) {
//            $price += $item->getUnitPrice() * $item->getQuantity();
//        }
//        return $price;
//    }
}