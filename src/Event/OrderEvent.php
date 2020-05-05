<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderEvent extends GenericEvent
{
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

}