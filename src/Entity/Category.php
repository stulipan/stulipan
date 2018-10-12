<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_category")
 */
class Category
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
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=false, unique=true)
     */
    private $slug='';

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
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
     *
     * @return Category
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

    public function getSlug()
    {
        return $this->slug;
    }



    public function setSlug($nev)
    {
        $this->slug = $nev;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

}