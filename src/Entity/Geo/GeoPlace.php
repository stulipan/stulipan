<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="geo_place")
 * @ORM\Entity(repositoryClass="App\Repository\GeoPlaceRepository")
 */
class GeoPlace implements \JsonSerializable
{
    /**
     * @var int
     * @Groups("main")
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="country_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg az országot.")
     */
    private $country='';

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=10, nullable=false)
     * @Assert\NotBlank(message="Add meg az országkódot.")
     */
    private $countryCode='';

    /**
     * @var string
     * @Groups("main")
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a települést.")
     */
    private $city;

    /**
     * @var int
     * @Groups("main")
     *
     * @Assert\Range(min=0, minMessage="Hibás irányítószám.")
     * @Assert\NotBlank(message="Add meg az irányítószámot.")
     * @ORM\Column(name="zip", type="integer", nullable=false)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a megyét.")
     */
    private $province='';

    /**
     * @var string
     *
     * @ORM\Column(name="district", type="string", length=50, nullable=false)
     * @Assert\NotBlank(message="Add meg a kerületet.")
     */
    private $district='';
    
    /**
     * @var GeoPrice
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Geo\GeoPrice", mappedBy="city")
     * @ ORM\JoinColumn(name="id", referencedColumnName="city_id", nullable=true)  /// Ez valamiert nem kell!!?
     */
    private $price;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            "id"            => $this->getId(),
            "country"       => $this->getCountry(),
            "countryCode"   => $this->getCountryCode(),
            "city"          => $this->getCity(),
            "zip"           => $this->getZip(),
            "province"      => $this->getProvince(),
            "district"      => $this->getDistrict(),
            "price"         => $this->getPrice(),
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
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @var string $country
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @var string $code
     */
    public function setCountryCode(?string $code)
    {
        $this->countryCode = $code;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @var string $city
     */
    public function setCity(?string $city)
    {
        $this->city = $city;
    }

    /**
     * @return int
     */
    public function getZip(): ?int
    {
        return $this->zip;
    }

    /**
     * @var int $zip
     */
    public function setZip(?int $zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getProvince(): ?string
    {
        return $this->province;
    }

    /**
     * @var string $province
     */
    public function setProvince(?string $province)
    {
        $this->province = $province;
    }

    /**
     * @return string
     */
    public function getDistrict(): ?string
    {
        return $this->district;
    }

    /**
     * @param string $district
     */
    public function setDistrict(?string $district)
    {
        $this->district = $district;
    }
    
    /**
     * @return GeoPrice|null
     */
    public function getPrice(): ?GeoPrice
    {
        return $this->price;
    }
    
    /**
     * @param GeoPrice|null $price
     */
    public function setPrice(?GeoPrice $price)
    {
        $this->price = $price;
    }

}
