<?php

namespace App\Entity;

final class Events
{
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
}