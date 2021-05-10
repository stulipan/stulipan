<?php
// src/Entity/Munkatars.php

namespace App\Entity\Boltzaras;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="munkatars")
 */
class Munkatars
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="munkatars_neve", length=100)
     */
    private $munkatarsNeve;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=1000, nullable=true)
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg" }, groups = {"create"})
     */
    private $avatar = '';


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

    /**
     * @return null|string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param null|string $image
     */
    public function setAvatar($image)
    {
        $this->avatar = $image;
    }


}