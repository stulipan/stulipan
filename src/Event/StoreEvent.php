<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

final class StoreEvent extends GenericEvent
{
    public const CART_CREATE = 'cart.create';
    public const CART_UPDATE = 'cart.update';

    public const CHECKOUT_CREATE = 'checkout.create';
    public const CHECKOUT_UPDATE = 'checkout.update';

    public const ORDER_CREATE = 'order.create';
    public const ORDER_UPDATE = 'order.update';
    public const ORDER_TRACK_CONVERSION = 'order.track.conversion';

    public const IMPORT_ITEMS_FROM_CART = 'import.items.from.cart';
    public const IMPORT_ITEMS_FROM_CHECKOUT = 'import.items.from.checkout';

    public const EMAIL_SEND_ORDER_CONFIRMATION = 'email.send.orderConfirmation';
    public const EMAIL_SEND_SHIPPING_CONFIRMATION = 'email.send.shippingConfirmation';

    public const CUSTOMER_CREATE = 'customer.create';
    public const CUSTOMER_UPDATE = 'customer.update';

    public function setSubject($subject = null)
    {
        $this->subject = $subject;
    }

}