<?php

namespace App\Entity\Geo;

use App\Entity\Price;
use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="delivery_geo_fee")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"city"}, message="Ez a település már rögzítve van!")
 */
class GeoPrice implements JsonSerializable
{
    use TimestampableTrait;

    /**
     * @Groups({"geoPriceView", "geoPriceList"})
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     */
    private $id;

    /**
     * @Groups({"geoPriceView", "geoPriceList"})
     *
     * @var GeoPlace
     *
     * @Assert\NotBlank(message="Válassz települést.")
     * @ORM\OneToOne(targetEntity="App\Entity\Geo\GeoPlace", inversedBy="price", cascade={"persist"})
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     */
    private $city;
    
    /**
     * @Groups({"geoPriceView", "geoPriceList"})
     * @var Price
     *
     * @Assert\NotBlank(message="Adj meg szállítá díjat.")
     * @ORM\OneToOne(targetEntity="App\Entity\Price", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id")
     */
    private $price;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            "id"        => $this->getId(),
            "city"      => $this->getCity(),
            'price'     => $this->getPrice(),
        ];
    }
    
    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @return GeoPlace
     */
    public function getCity(): GeoPlace
    {
        return $this->city;
    }
    
    /**
     * @param GeoPlace $city
     */
    public function setCity(GeoPlace $city)
    {
        $this->city = $city;
    }
    
    /**
     * @return Price
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }
    
    /**
     * @param Price|null $price
     */
    public function setPrice(?Price $price)
    {
        $this->price = $price;
    }
}