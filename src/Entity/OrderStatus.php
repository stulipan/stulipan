<?php

namespace App\Entity;
//use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 *
 * @ORM\Entity
 * @ORM\Table(name="cart_order_status")
 */
class OrderStatus implements JsonSerializable
{
    public const STATUS_CREATED = 'created'; // rendelés létrehozva
    public const STATUS_PAYMENT_PENDING = 'pending'; // fizetésre vár
    public const STATUS_PAYMENT_FAILED = 'failed'; // fizetésre sikertelen
    public const STATUS_PAYMENT_REFUNDED = 'refunded'; // összeg visszafizetve

    public const STATUS_SENT = 'sent'; // elküldve, azaz szállítás alatt
    public const STATUS_FULFILLED = 'fulfilled'; // teljesítve
    public const STATUS_RETURNED = 'returned'; // visszaküldve

    public const STATUS_REJECTED = 'rejected'; // elutasítva - ezt még nem tudom mikor kell használni
    public const STATUS_DELETED = 'deleted'; // törölve

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