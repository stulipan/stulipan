<?php

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="delivery_special_date")
 * @ORM\Entity(repositoryClass="App\Repository\DeliverySpecialDateRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("specialDate", message="Ez a dátum már rögzítve van!")
 */
class DeliverySpecialDate
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="smallint", name="id", length=11)
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Válassz dátumot.")
     * @ORM\Column(type="date", name="special_date", nullable=false)
     */
    private $specialDate;

    /**
     * @Assert\NotBlank(message="Válassz típust.")
     * @ORM\ManyToOne(targetEntity="App\Entity\DeliveryDateType")
     * @ORM\JoinColumn(name="date_type_id", referencedColumnName="id")
     */
    private $dateType;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * @param mixed $dateType
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
    }

    /**
     * @return mixed
     */
    public function getSpecialDate()
    {
        return $this->specialDate;
    }

    /**
     * @param mixed $specialDate
     */
    public function setSpecialDate($specialDate)
    {
        $this->specialDate = $specialDate;
    }

//    public function setDate(\DateTime $datum = null)
//    {
//        $this->date = $datum;
//    }

    /**
     * @return DeliveryDateType
     */
    public function getKind(): ?DeliveryDateType
    {
        return $this->dateType;
    }

    /**
     * @param DeliveryDateType $type
     *
     */
    public function setKind(?DeliveryDateType $type): void
    {
        $this->dateType = $type;
    }

}