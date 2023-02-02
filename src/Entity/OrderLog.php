<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="cart_order_history")
 * @ORM\Entity
 * (repositoryClass="App\Repository\PaymentRepository")
 */

class OrderLog
{
    public const CHANNEL_CHECKOUT = 'checkout';
    public const CHANNEL_ADMIN = 'admin';

    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     * @ Assert\NotBlank(message="Az előzmény üzenete hiányzik!")
     * @ Assert\NotBlank(message="A komment üres!", groups={"hasznald_ezt_a_formban"})
     * @Assert\NotBlank(message="order.history.comment-is-missing", groups={"hasznald_ezt_a_formban"})
     */
    private $message;

    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var OrderLogChannel
     *
     * @ORM\ManyToOne(targetEntity="OrderLogChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false)
     */
    private $channel;

    /**
     * @var Order
     *
     * ==== Many History entries in one Order ====
     * ==== inversed By="logs" => az Order entitásban definiált 'logs' attibútumról van szó;
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="logs")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy előzmény kell legyen a rendelésben.")
     */
    private $order;

    /**
     * @var bool
     *
     * @ORM\Column(name="comment", type="smallint", length=1, nullable=false, options={"default"=0})
     */
    private $comment = 0;


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getMessage();
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
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return OrderLogChannel
     */
    public function getChannel(): ?OrderLogChannel
    {
        return $this->channel;
    }

    /**
     * @param OrderLogChannel $channel
     */
    public function setChannel(OrderLogChannel $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return Order
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isComment(): bool
    {
        return 1 !== $this->comment ? false : true;
    }

    /**
     * @param bool $comment
     */
    public function setComment(bool $comment): void
    {
        $this->comment = $comment;
    }

}