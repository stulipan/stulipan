<?php

namespace App\Entity\Boltzaras;

use App\Entity\InventoryInvoiceCompany;
use App\Entity\TimestampableTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="inv_invoice")
 * @ORM\Entity(repositoryClass="App\Repository\Boltzaras\InventoryInvoiceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InventoryInvoice
{

    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
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
     * @var InventoryInvoiceCompany
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Boltzaras\InventoryInvoiceCompany", inversedBy="invoices")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * @ ORM\OrderBy({"company_name"="ASC"})
     *
     * @Assert\NotBlank(message="Válassz egy beszállítócéget.")
     */
    private $company;


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

    public function getCompany()
    {
        return $this->company;
    }

    // setter methods

    public function setDatum(DateTime $datum = null)
    {
        $this->datum = $datum;
    }

    public function setOsszeg($osszeg)
    {
        $this->osszeg = $osszeg;
    }

    public function setCompany($ceg)
    {
        $this->company = $ceg;
    }


}