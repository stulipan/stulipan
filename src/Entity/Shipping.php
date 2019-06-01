<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//* @MappedSuperclass
/**
 *
 * @ORM\Table(name="cart_shipping")
 * @ORM\Entity(repositoryClass="App\Repository\ShippingRepository")
 */

class Shipping
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="A szállítási mód megnevezése hiányzik!")
     * @ORM\Column(name="shipping_name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     * @ Assert\NotBlank(message="A szállítási mód rövid leírása hiányzik!")
     */
    private $description;

    /**
     * @var float
     *
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $price;

    /**
     * @var int
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
    private $enabled;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
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
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getOrdering(): ?int
    {
        return (int) $this->ordering;
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
//        dump(1 != $this->enabled ? false : true);die;
//        return $this->enabled;
        return 1 !== $this->enabled ? false : true;
    }

    /**
     * Returns false or true, after transformation (1 or 0 which are stored in db)
     * @return bool
     */
    public function isEnabled(): bool
    {
        return 1 !== $this->enabled ? false : true;
    }

    /**
     * Sets value to 1 or 0 which are stored in db
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
//        dump($enabled);die;
        $this->enabled = true === $enabled ? 1 : 0;
//        dump($this->getEnabled());die;
    }
}