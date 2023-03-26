<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use App\Services\PaymentBuilder;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Table(name="payment_method")
 * @ORM\Entity(repositoryClass="App\Repository\PaymentMethodRepository")
 * @UniqueEntity("shortcode", message="Ez a shortcode már használatban van!")
 */

class PaymentMethod
{
    public const CREDIT_CARD = 'cib';
    public const PAYPAL = 'paypal';
    public const BANK_TRANSFER ='bank';
    public const BARION = 'barion';

    use TimestampableTrait;

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
     * @ORM\Column(name="description", type="text", nullable=false)
     * @ Assert\NotBlank(message="A fizetési mód részletes leírása hiányzik!")
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
     * @Assert\NotBlank(message="Adj meg egy összeget.")
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"=0.00})
     */
    private $price = 0;

    /**
     * @var int
     * @Groups({"orderView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", type="smallint", nullable=false, options={"default"=100, "unsigned"=true})
     */
    private $ordering;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="smallint", nullable=false, options={"default"=0})
     */
    private $enabled = false;


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
    
    /**
     * @return string|null
     */
    public function getShortcode(): ?string
    {
        return $this->shortcode;
    }
    
    /**
     * @param string|null $shortcode
     */
    public function setShortcode(?string $shortcode)
    {
        $this->shortcode = $shortcode;
    }

    /**
     * @return string|null
     */
    public function getShort(): ?string
    {
        return $this->short;
    }

    /**
     * @param string|null $description
     */
    public function setShort(?string $description): void
    {
        $this->short = $description;
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
     * @return float|null
     */
    public function getPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int|null
     */
    public function getOrdering(): ?int
    {
        return (int) $this->ordering;
    }

    /**
     * @param int|null $ordering
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
        return PaymentBuilder::MANUAL_BANK === $this->getShortcode() ? true : false;
    }

    /**
     * @return bool
     */
    public function isManualPayment(): bool
    {
        return (PaymentBuilder::MANUAL_BANK === $this->getShortcode() || PaymentBuilder::MANUAL_COD === $this->getShortcode()) ? true : false;
    }
}