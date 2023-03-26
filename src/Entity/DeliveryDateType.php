<?php

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="delivery_date_type")
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryDateTypeRepository")
 * @ORM\HasLifecycleCallbacks
 * @ UniqueEntity("default", message="Csak egy alapértelmezett típus lehet!")
 */
class DeliveryDateType
{
    use TimestampableTrait;

    public const IS_DEFAULT = 1; // ha 1, akkor ez a default dátum típus

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id", nullable=false, options={"unsigned"=true})
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Adj meg egy nevet a dátumtípusnak.")
     * @ORM\Column(name="type_name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="default", type="boolean", nullable=false, options={"default"=false})
     */
    private $default;

    /**
     * @var DeliveryDateInterval[]|ArrayCollection|null
     *
     * @ORM\OneToMany(targetEntity="App\Entity\DeliveryDateInterval", mappedBy="dateType", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="date_type_id")
     * @ORM\OrderBy({"ordering" = "ASC"})
     *
     * @Assert\Collection()
     */
    private $intervals;

    /**
     * @var DeliverySpecialDate[]|ArrayCollection|null
     *
     * @ORM\OneToMany(targetEntity="App\Entity\DeliverySpecialDate", mappedBy="dateType")
     * @ ORM\JoinColumn(name="id", referencedColumnName="date_type_id")
     * @ORM\OrderBy({"ordering" = "ASC"})
     *
     * @ Assert\Collection()
     */
    private $specialDates;

    public function __construct()
    {
        $this->intervals = new ArrayCollection();
        $this->specialDates = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     *
     */
    public function setDefault()
    {
        $this->default = self::IS_DEFAULT;
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

    public function __toString()
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
     * @param DeliveryDateInterval $item
     */
    public function addInterval(DeliveryDateInterval $item): void
    {
        $item->setDateType($this);
        if (!$this->intervals->contains($item)) {
            $this->intervals->add($item);
        }
    }

    /**
     * @param DeliveryDateInterval $item
     */
    public function removeInterval(DeliveryDateInterval $item): void
    {
        $this->intervals->removeElement($item);
    }

    /**
     * @return Collection|DeliveryDateInterval[]
     */
    public function getIntervals(): ?Collection
    {
        return $this->intervals;
    }

    /**
     * @return bool
     */
    public function hasIntervals(): bool
    {
        return $this->getIntervals()->isEmpty() ? false : true;
    }

}