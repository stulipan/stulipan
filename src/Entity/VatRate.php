<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="vat_rate")
 * @ORM\HasLifecycleCallbacks()
 *
 *
 */
class VatRate implements JsonSerializable
{
    const DEFAULT_VAT_RATE = 1; //id=1 a VatRate db tablaban
    
    /**
     * @var int
     * @Groups({"productView"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150, nullable=false)
     */
    private $name;
    
    /**
     * @var float
     * @Groups({"productView"})
     *
     */
    private $value;

    /**
     * @var VatValue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VatValue", mappedBy="vatRate")
     * @ORM\OrderBy({"expiresAt" = "DESC"})
     */
    private $values;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct()
    {
        $this->values = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
//            'name'      => $this->getName(),
            'value'     => $this->getValue(),
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    public function __toString(): string
    {
        return $this->getName();
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Collection|VatValue[]
     */
    public function getValues(): Collection
    {
        return $this->values;
    }
    
    /**
     * A VatRate may have several values, each value has an expiresAt date to it, except for one which will become active when
     * a value with the expiresAt date reaches and surpasses the current date.
     *
     * The bellow function is executed immediately after loading all values from db, thus sets the actual current VAT value.
     *
     * @ORM\PostLoad()
     */
    public function setCurrentValue()
    {
        // Checks if there is a value with Active state.
        /** @var ArrayCollection $filteredValues */
        $filteredValues = $this->values->filter(
            function($entry) {
                /** @var VatValue $entry */
                return true === $entry->isActive() ? $entry : null;
            }
        );
        if ($filteredValues) {
            if ($filteredValues->count() == 1) {
                $this->value = $filteredValues->first()->getValue();
            } else {
                // If no Active value was found, will look for a value that has the closest expiresAt date. There should be only one!
                // First, eliminates the entries with expiresAt === null.
                $notNullDateValues = $this->values->filter(
                    function ($entry) {
                        /** @var VatValue $entry */
                        return $entry->getExpiresAt() !== null ? $entry : null;
                    }
                );
                // In the remaining entries, looks for the closest expiresAt date, which is in the future.
                $higherDateValue = $notNullDateValues->filter(
                    function ($entry) {
                        /** @var VatValue $entry */
                        return ($entry->getExpiresAt()->diff(new DateTime())->days <= 1) ? $entry : null;
                    }
                );
                if (!$higherDateValue->isEmpty()) {
                    $this->value = $higherDateValue->first()->getValue();
                } else {
                    // If there is no date in the future, looks for entries that have no expiresAt date. There should be only one!
                    $nullDateValues = $this->values->filter(
                        function ($entry) {
                            /** @var VatValue $entry */
                            return $entry->getExpiresAt() === null ? $entry : null;
                        }
                    );
                    $this->value = $nullDateValues->first()->getValue();
                }
            }
        }
    }
    
    /**
     * @return float
     */
    public function getValue(): float
    {
        return (float) $this->value;
    }
}