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
     * @ORM\Column(name="cost", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $cost;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="order", nullable=false, options={"default"="100"})
     */
    private $order;

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
     * @return float
     */
    public function getCost(): float
    {
        return (float) $this->cost;
    }

    /**
     * @param float $cost
     */
    public function setCost(float $cost): void
    {
        $this->cost = $cost;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setPrice(int $order): void
    {
        $this->order = $order;
    }
}