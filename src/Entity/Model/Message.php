<?php

declare(strict_types=1);

namespace App\Entity\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */

class Message
{
    /**
     * @var string
     * Assert\NotNull(message="sdfsdfsdf")
     */
    private $message;

    /**
     * @var string
     *
     * Assert\NotBlank()
     */
    private $messageAuthor;

    public function __construct(string $message = null, string $messageAuthor= null)
    {
        $this->message = $message;
        $this->messageAuthor = $messageAuthor;
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
    public function getMessageAuthor(): ?string
    {
        return $this->messageAuthor;
    }

    /**
     * @param string $messageAuthor
     */
    public function setMessageAuthor(?string $messageAuthor)
    {
        $this->messageAuthor = $messageAuthor;
    }

}