<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="delivery_interval")
 * @ORM\Entity
 */

class DeliveryDateInterval
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var DeliveryDateType
     *
     * ==== Many DateIntervals belong to one DateType ====
     * ==== inversed By="intervals" => az DateType entitásban definiált 'intervals' attibútumról van szó;
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\DeliveryDateType", inversedBy="intervals")
     * @ORM\JoinColumn(name="date_type_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Legalább egy termék kell legyen a rendelésben.")
     */
    private $dateType;

    /**
     * @Assert\NotBlank(message="Adj egy nevet az idősávnak.")
     * @ORM\Column(name="interval_name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var float|null
     * @Assert\NotBlank(message="Hibás! {{ value }}")
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false)
     * @ Assert\Range(min=-0.01, minMessage="Az idősáv ára nem lehet negatív.")
     */
    private $price;

    /**
     * @var int|null
     * @ORM\Column(name="delivery_limit", type="integer", nullable=true)
     */
    private $deliveryLimit;

    /**
     * @var int
     *
     * @ Assert\NotBlank(message="Hiányzik!")
     * @ORM\Column(name="ordering", type="smallint", nullable=false, options={"default"=100, "unsigned"=true})
     */
    private $ordering;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DeliveryDateType
     */
    public function getDateType(): ?DeliveryDateType
    {
        return $this->dateType;
    }

    /**
     * @param DeliveryDateType $type
     */
    public function setDateType(DeliveryDateType $type): void
    {
        $this->dateType = $type;
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
        return (string) $this->getName();
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

        $this->price = null === $price ? 0 : $price;
//        $this->price = $price;
    }

    /**
     * @return int|null
     */
    public function getDeliveryLimit(): ?int
    {
        return $this->deliveryLimit;
    }

    /**
    * @param int|null $deliveryLimit
    */
    public function setDeliveryLimit(?int $deliveryLimit): void
    {
        $this->deliveryLimit = $deliveryLimit;
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
}