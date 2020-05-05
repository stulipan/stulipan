<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//* @MappedSuperclass
/**

 *
 * @ORM\Table(name="cart_payment")
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 * @UniqueEntity("shortcode", message="Ez a shortcode már használatban van!")
 */

class Payment
{
    use TimestampableTrait;

    private const BANK_TRANSFER = 3; // the value is from db

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
     * @Assert\NotBlank(message="A fizetési mód megnevezése hiányzik!")
     * @ORM\Column(name="payment_name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\NotBlank(message="A rövid kód hiányzik!")
     * @ORM\Column(name="shortcode", type="string", length=10, nullable=false)
     */
    private $shortcode;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="short", type="string", length=255, nullable=false)
     * @ Assert\NotBlank(message="A fizetési mód rövid rövid leírása hiányzik!")
     */
    private $short;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     * @ Assert\NotBlank(message="A fizetési mód rövid leírása hiányzik!")
     */
    private $description;

    /**
     * @var string|null
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="image", type="string", length=1000, nullable=true)
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg" }, groups = {"create"})
     */
    private $image = '';

    /**
     * @var float
     * @Groups({"orderView", "orderList"})
     *
     * @ Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $price;

    /**
     * @var int
     * @Groups({"orderView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", nullable=true, options={"default"="100"})
     */
    private $ordering;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="smallint", nullable=false, options={"default"="1"})
     */
    private $enabled = '1';


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
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
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
    public function getShort(): ?string
    {
        return $this->short;
    }

    /**
     * @param string $description
     */
    public function setShort(string $description): void
    {
        $this->short = $description;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param null|string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    /**
     * @param int $ordering
     */
    public function setOrdering(?int $ordering): void
    {
        $this->ordering = $ordering;
    }

    /**
     * @return bool
     */
    public function getEnabled(): ?bool
    {
        return 1 !== $this->enabled ? false : true;
//            $this->enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return 1 !== $this->enabled ? false : true;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isBankTransfer(): bool
    {
        return self::BANK_TRANSFER == $this->id ? true : false;
    }

}