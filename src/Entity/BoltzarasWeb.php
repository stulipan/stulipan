<?php
// src/Entity/Boltzaras.php

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="boltzaras_web")
 * @ORM\Entity(repositoryClass="App\Repository\BoltzarasWebRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BoltzarasWeb
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="smallint", name="id", length=5)
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Válassz dátumot.")
     * @ORM\Column(type="month", type="date", nullable=false)
     */
    private $month;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="amount", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $amount;

    public function getId()
    {
        return $this->id;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function setMonth(\DateTime $datum = null)
    {
        $this->month = $datum;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($osszeg)
    {
        $this->amount = $osszeg;
    }
}