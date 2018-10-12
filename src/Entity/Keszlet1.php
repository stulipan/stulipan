<?php

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="keszlet")
 * @ ORM\Entity(repositoryClass="App\Repository\KeszletRepository")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Keszlet1
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
     * @ORM\Column(name="datum", type="date", nullable=false)
     */
    private $datum;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Adj nevet a terméknek.")
     * @ORM\Column(name="termeknev", type="string", length=255, nullable=true)
     */
    private $termeknev = '';

    /**
     * @var int|null
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="A készlet nem lehet negatív.")
     * @ORM\Column(name="stock", type="smallint", nullable=true)
     */
    private $stock = 0;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="cog", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $cog;

    /**
     * @ Assert\NotBlank()
     * @ Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="szorzo", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $szorzo;

    /**
     * @ var float
     * @ Assert\NotBlank()
     * @ A ssert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="gross_price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $grossPrice = 0;



    // getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getDatum()
    {
        return $this->datum;
    }

    public function setDatum(\DateTime $datum = null)
    {
        $this->datum = $datum;
    }

    public function getTermeknev()
    {
        return $this->termeknev;
    }

    /**
     * Set termeknev
     *
     * @param string $termeknev
     */
    public function setTermeknev($termeknev)
    {
        $this->termeknev = $termeknev;
    }

    /**
     * @return int|null
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int|null $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    public function getCog()
    {
        return $this->cog;
    }

    /**
     * @param float|null $cog
     */
    public function setCog($cog)
    {
        $this->cog = $cog;
    }



    public function getSzorzo()
    {
        return $this->szorzo;
    }

    /**
     * @param float|null $szorzo
     */
    public function setSzorzo($szorzo)
    {
        $this->szorzo = $szorzo;
    }

    /**
     * @return float
     */
    public function getGrossPrice()
    {
        return (float) $this->grossPrice;
    }

    /**
     * @param $grossPrice
     */
    public function setGrossPrice($grossPrice)
    {
        $this->grossPrice = $grossPrice;
    }


}