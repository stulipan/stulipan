<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderEvent extends GenericEvent
{
    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const PRODUCT_ADDED_TO_CART = 'product.added';


    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const PAYMENT_UPDATED = 'order.paymentStatusUpdated';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const ORDER_CREATED = 'order.created';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const ORDER_UPDATED = 'order.updated';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const DELIVERY_DATE_UPDATED = 'order.deliveryDateUpdated';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const SET_ORDER_AS_TRACKED = 'conversionTracking.loaded';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const EMAIL_SENT_ORDER_CONFIRMATION = 'email.sent.orderConfirmation';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     * @var string
     */
    public const EMAIL_SENT_SHIPPING_CONFIRMATION = 'email.sent.shippingConfirmation';
}