<?php

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="szamla")
 * @ORM\Entity(repositoryClass="App\Repository\SzamlaRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Szamla
{

    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="smallint", name="id", length=11)
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Válassz dátumot.")
     * @ORM\Column(type="date", nullable=false)
     */
    private $datum;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(type="decimal", precision=20, scale=2, nullable=false)
     */
    private $osszeg;

    /**
     *
     * @var string
     *
     * @Assert\NotBlank(message="Válassz beszállítót.")
     * @ORM\Column(name="beszallito", type="string", length=100, nullable=false)
     *
     * // @  ORM\ManyToOne(targetEntity="App\Entity\Beszallito")
     * // @  ORM\JoinColumn(name="munkatars_id", referencedColumnName="munkatars_id")
     */
    private $beszallito;


    // getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getDatum()
    {
        return $this->datum;
    }

    
    public function getOsszeg()
    {
        return $this->osszeg;
    }

    public function getBeszallito()
    {
        return $this->beszallito;
    }

    // setter methods

    public function setDatum(\DateTime $datum = null)
    {
        $this->datum = $datum;
    }

    public function setOsszeg($osszeg)
    {
        $this->osszeg = $osszeg;
    }

    public function setBeszallito($ceg)
    {
        $this->beszallito = $ceg;
    }


}