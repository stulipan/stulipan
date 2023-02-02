<?php

namespace App\Entity\Boltzaras;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="inv_company")
 */
class InventoryInvoiceCompany
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Add meg a cég nevét.")
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