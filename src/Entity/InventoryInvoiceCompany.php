<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="inv_company")
 */
class InventoryInvoiceCompany
{

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=100, nullable=false)
     */
    private $company;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InventoryInvoice", mappedBy="company")
     *
     */
    private $invoices;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set company name
     *
     * @param string $nev
     *
     * @return InventoryInvoiceCompany
     */
    public function setCompany($nev)
    {
        $this->company = $nev;

        return $this;
    }

    /**
     * Get company name
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    public function __toString(): string
    {
        return $this->getCompany();
    }


    /**
     * @return Collection|InventoryInvoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

}