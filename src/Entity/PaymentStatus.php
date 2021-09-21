<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="cart_order_payment_status")
 * @UniqueEntity("shortcode", message="Ez a shortcode már használatban van!")
 */
class PaymentStatus implements JsonSerializable
{

    public const STATUS_PENDING = 'pending'; // The payments are pending. Payment might fail in this state. Check again to confirm whether the payments have been paid successfully.
    public const STATUS_AUTHORIZED = 'authorized'; // The payments have been authorized.

    public const STATUS_PARTIALLY_PAID = 'partially-paid'; // The order have been partially paid.
    public const STATUS_PAID = 'paid'; // The payments have been paid.

    public const STATUS_PARTIALLY_REFUNDED = 'partially-refunded'; // The payments have been partially refunded.
    public const STATUS_REFUNDED = 'refunded'; // The payments have been refunded.
    public const STATUS_VOIDED = 'voided'; // The payments have been voided.


    /**
     * @var int
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="status_name", type="string", length=100, nullable=false)
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
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="css", type="string", length=20, nullable=false)
     */
    private $css;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'shortcode'     => $this->getShortcode(),
            'css'           => $this->getCss(),
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
    public function setName(?string $name)
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
    public function getShortcode(): ?string
    {
        return $this->shortcode;
    }
    
    /**
     * @param string $shortcode
     */
    public function setShortcode(?string $shortcode)
    {
        $this->shortcode = $shortcode;
    }
    
    /**
     * @return string
     */
    public function getCss(): ?string
    {
        return $this->css;
    }
    
    /**
     * @param string $css
     */
    public function setCss(string $css)
    {
        $this->css = $css;
    }
    

}