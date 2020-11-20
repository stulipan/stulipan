<?php

namespace App\Entity\Boltzaras;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="inv_product_category")
 */
class InventoryCategory
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
     * @ORM\Column(name="category_name", type="string", length=100, nullable=false)
     */
    private $categoryName='';

    /**
     * @ Assert\NotBlank()
     * @ Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="markup", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $markup;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InventoryProduct", mappedBy="category")
     *
     */
    private $products;

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
     * Set category name
     *
     * @param string $nev
     * @return InventoryCategory
     */
    public function setCategoryName($nev)
    {
        $this->categoryName = $nev;

        return $this;
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    public function __toString(): string
    {
        return $this->getCategoryName();
    }

    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * @param float|null $szorzo
     */
    public function setMarkup($szorzo)
    {
        $this->markup = $szorzo;
    }


    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

}