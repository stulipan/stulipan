<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\Constraints as CustomAssert;

/**
 * @CustomAssert\GreetingCardIsValid()
 */

class CartGreetingCard
{
    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     */
    private $message;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     */
    private $author;

    public function __construct(string $message = null, string $author= null)
    {
        $this->message = $message;
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(?string $author)
    {
        $this->author = $author;
    }

}