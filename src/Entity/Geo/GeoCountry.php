<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="geo_country")
 * @ORM\Entity
 */
class GeoCountry
{
//    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=75, nullable=false)
     * @Assert\NotBlank(message="Add meg az országot.")
     */
    private $name='';

    /**
     * @var string
     *
     * @ORM\Column(name="alpha_2", type="string", length=2, nullable=false)
     * @Assert\NotBlank(message="Add meg az aplha2 kódót.")
     */
    private $alpha2='';

    /**
     * @var string
     *
     * @ORM\Column(name="alpha_3", type="string", length=3, nullable=false)
     * @Assert\NotBlank(message="Add meg az aplha3 kódót.")
     */
    private $alpha3='';

//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="App\Entity\Address", mappedBy="country")
//     */
//    private $addresses;
//
//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="App\Entity\OrderAddress", mappedBy="country")
//     */
//    private $orderAddresses;
//
//    public function __construct()
//    {
//        $this->addresses = new ArrayCollection();
//        $this->orderAddresses = new ArrayCollection();
//    }
//
//    /**
//     * @return Collection|Address[]
//     */
//    public function getAddresses(): Collection
//    {
//        return $this->addresses;
//    }
//
//    /**
//     * @return Collection|OrderAddress[]
//     */
//    public function getOrderAddresses(): Collection
//    {
//        return $this->orderAddresses;
//    }




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

    public function __toString(): string
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
     * @return string
     */
    public function getAlpha2(): string
    {
        return $this->alpha2;
    }

    /**
     * @param string $alpha2
     */
    public function setAlpha2(string $alpha2)
    {
        $this->alpha2 = $alpha2;
    }

    /**
     * @return string
     */
    public function getAlpha3(): string
    {
        return $this->alpha3;
    }

    /**
     * @param string $alpha3
     */
    public function setAlpha3(string $alpha3)
    {
        $this->alpha3 = $alpha3;
    }
}
