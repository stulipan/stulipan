<?php
// src/Entity/Munkatars.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="munkatars")
 */
class Munkatars
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="smallint", name="munkatars_id", length=3)
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="munkatars_neve", length=100)
     */
    private $munkatarsNeve;


    // getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getMunkatarsNeve()
    {
        return $this->munkatarsNeve;
    }

    // setter methods
    public function setMunkatarsNeve($nev)
    {
        $this->munkatarsNeve = $nev;
    }

    public function __toString()
    {
        return $this->getMunkatarsNeve();
    }


}