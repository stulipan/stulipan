<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="shipping_method")
 * @ORM\Entity(repositoryClass="App\Repository\ShippingMethodRepository")
 */

class ShippingMethod
{
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
     * @Groups({"orderView", "orderList"})
     *
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"=0.00})
     */
    private $price;

    /**
     * @var int
     * @Groups({"orderView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", type="smallint", nullable=false, options={"default"=100, "unsigned"=true})
     * @ORM\OrderBy({"ordering" = "ASC"})
     */
    private $ordering;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="smallint", nullable=false, options={"default"=0})
     */
    private $enabled = false;

    /**
     * @return int|null
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
     * @return float|null
     */
    public function getPrice(): ?float
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