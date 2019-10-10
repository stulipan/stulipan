<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\Model\CartCard;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class MessageAndCustomer
{
    /**
     * @var CartCard
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\Valid()   This is required so that validation at CartCard class level validation is triggered!
     */
    private $card;

    /**
     * @var CustomerBasic
     * @Groups({"orderView", "orderList"})
     *
     * @ Assert\Valid()
     */
    private $customer;


    public function __construct(CartCard $card = null, CustomerBasic $customer = null)
    {
        $this->card = $card;
        $this->customer = $customer;
    }

    /**
     * @return CartCard
     */
    public function getCard(): ?CartCard
    {
        return $this->card;
    }

    /**
     * @param CartCard $card
     */
    public function setCard(?CartCard $card)
    {
        $this->card = $card;
    }

    /**
     * @return CustomerBasic
     */
    public function getCustomer(): ?CustomerBasic
    {
        return $this->customer;
    }

    /**
     * @param CustomerBasic $customer
     */
    public function setCustomer(?CustomerBasic $customer)
    {
        $this->customer = $customer;
    }
    
}