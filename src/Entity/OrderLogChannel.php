<?php

namespace App\Entity;
//use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="cart_order_history_channel")
 * @UniqueEntity("shortcode", message="Ez a shortcode már használatban van!")
 */
class OrderLogChannel implements JsonSerializable
{

    public const CHECKOUT = 'checkout';
    public const ADMIN = 'admin';
    /**
     * @var int
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="channel_name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="shortcode", type="string", length=20, nullable=false)
     */
    private $shortcode;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'shortcode'     => $this->getShortcode(),
        ];
    }
    
    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->getName();
    }
    
    /**
     * @return string
     */
    public function getShortcode(): string
    {
        return $this->shortcode;
    }
    
    /**
     * @param string $shortcode
     */
    public function setShortcode(string $shortcode)
    {
        $this->shortcode = $shortcode;
    }

    /**
     * @return bool
     */
    public function isCheckout(): bool
    {
        return self::CHECKOUT == $this->getShortcode() ? true : false;
    }

}