<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Model\CustomerBasic;
use App\Entity\Recipient;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutRecipientAndCustomer
{
    /**
     * @var Recipient
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\Valid()   This is required for validation to be triggered at Recipient class level!
     */
    private $recipient;

    /**
     * @var CustomerBasic
     * @Groups({"orderView", "orderList"})
     */
    private $customer;


    public function __construct(Recipient $recipient = null, CustomerBasic $customer = null)
    {
        $this->recipient = $recipient;
        $this->customer = $customer;
    }

    /**
     * @return Recipient
     */
    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    /**
     * @param Recipient $recipient
     */
    public function setRecipient(Recipient $recipient): void
    {
        $this->recipient = $recipient;
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