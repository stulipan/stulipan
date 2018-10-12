<?php

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="inv_product")
 * @ORM\Entity(repositoryClass="App\Repository\InventoryProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InventoryProduct
{

    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="smallint", name="id", length=11)
     */
    private $id;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Adj nevet a terméknek.")
     * @ORM\Column(name="product_name", type="string", length=255, nullable=true)
     */
    private $productName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer")
     */
    private $categoryId;

    /**
     *
     * @var InventoryCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\InventoryCategory", inversedBy="products")
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
        return $this->categoryId;
    }

    /**
     * @var int $categoryId
     *
     */
    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;

    }

    /**
     * @return InventoryCategory
     */
    public function getCategory(): ?InventoryCategory
    {
        return $this->category;
    }

    /**
     * @var InventoryCategory $category
     *
     */
    public function setCategory(?InventoryCategory $category): void
    {
        $this->category = $category;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set product name
     *
     * @var string $productName
     *
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }


}