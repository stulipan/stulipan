<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Entity\Model\Message;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AssertApp;

/**
 *
 */
class MessageAndCustomer
{
    /**
     * @var Message
     *
     * @Assert\NotNull()
     * AssertApp\MessageWithAuthor()
     */
    private $message;

    /**
     * @var CustomerBasic
     *
     * Assert\NotNull()
     */
    private $customer;


    public function __construct(Message $message = null, CustomerBasic $customer = null)
    {
        $this->message = $message;
        $this->customer = $customer;
    }

    /**
     * @return Message
     */
    public function getMessage(): ?Message
    {
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage(?Message $message)
    {
        $this->message = $message;
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