<?php

namespace App\Entity\Boltzaras;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="inv_product")
 * @ORM\Entity(repositoryClass="App\Repository\Boltzaras\InventoryProductRepository")
 * \HasLifecycleCallbacks
 */
class InventoryProduct
{

    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $id;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Adj nevet a terméknek.")
     * @ORM\Column(name="product_name", type="string", length=255, nullable=true)
     */
    private $productName;

//    /**
//     * @var int
//     *
//     * @ORM\Column(name="category_id", type="integer")
//     */
//    private $categoryId;

    /**
     *
     * @var InventoryCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Boltzaras\InventoryCategory", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Válassz egy kategóriát.")
     */
    private $category;



    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->category->getId();
    }
//
//    /**
//     * @var int $categoryId
//     *
//     */
//    public function setCategoryId(int $categoryId): void
//    {
//        $this->categoryId = $categoryId;
//    }

    /**
     * @return InventoryCategory
     */
    public function getCategory(): ?InventoryCategory
    {
        return $this->category;
    }

    /**
     * @var InventoryCategory|null $category
     *
     */
    public function setCategory(?InventoryCategory $category): void
    {
        $this->category = $category;
    }

    public function getProductName(): ?string
    {
        return (string) $this->productName;
    }

    /**
     * Set product name
     *
     * @var string|null $productName
     */
    public function setProductName(?string $productName)
    {
        $this->productName = $productName;
    }


}