<?php

namespace App\Entity\Boltzaras;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="boltzaras")
 * @ORM\Entity(repositoryClass="App\Repository\Boltzaras\BoltzarasRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Boltzaras
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Válassz dátumot.")
     * @ORM\Column(type="date", nullable=false)
     */
    private $idopont;

    //itt nem kell megadjam a name="modositas_idopontja" oszlopotnevet, mert by-default erre konvertalja a valtozot
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $modositasIdopontja;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    private $kassza;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    private $bankkartya;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    private $keszpenz;

    /**
     * @Assert\NotBlank(message="Válassz munkatársat.")
     * @ORM\ManyToOne(targetEntity="App\Entity\Boltzaras\Munkatars")
     * @ORM\JoinColumn(name="munkatars_id", referencedColumnName="id")
     */
    private $munkatars;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;


    // getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getIdopont()
    {
        return $this->idopont;
    }

    public function getModositasIdopontja()
    {
        return $this->modositasIdopontja;
    }

    public function getKassza()
    {
        return $this->kassza;
    }

    public function getBankkartya()
    {
        return $this->bankkartya;
    }

    public function getKeszpenz()
    {
        return $this->keszpenz;
    }

    public function getMunkatars()
    {
        return $this->munkatars;
    }

    // setter methods

    public function setIdopont(DateTime $datum = null)
    {
        $this->idopont = $datum;
    }


    public function setModositasIdopontja()
    {
        $datum = new DateTime();
        $datum->format('Y-m-d H:i:s');
        $this->modositasIdopontja = $datum;
    }

    public function setKassza($osszeg)
    {
        $this->kassza = $osszeg;
    }

    public function setBankkartya($osszeg)
    {
        $this->bankkartya = $osszeg;
    }

    public function setKeszpenz($osszeg)
    {
        $this->keszpenz = $osszeg;
    }

    public function setMunkatars(Munkatars $ember)
    {
        $this->munkatars = $ember;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }
}