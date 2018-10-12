<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
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
     * @var string|null
     * @Assert\NotBlank(message="Nem adtál meg SKU-t.")
     * @ORM\Column(name="sku", type="string", length=20, nullable=true)
     */
    private $sku;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Adj nevet a terméknek.")
     * @ORM\Column(name="product_name", type="string", length=100, nullable=true)
     */
    private $productName = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @ var float
     * @ Assert\NotBlank()
     * @ A ssert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
     * @ORM\Column(name="gross_price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $grossPrice = 0;

    /**
     * @ var float
     *
     * @ORM\Column(name="promo_price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $promoPrice = 0;

    /**
     * @ var float
     *
     * @ORM\Column(name="net_price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $netPrice = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(name="thumb", type="string", length=1000, nullable=true)
     */
    private $thumb;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=1000, nullable=true)
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg" }, groups = {"create"})
     */
    private $image = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    /**
     * @var int|null
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="A készlet nem lehet negatív.")
     * @ORM\Column(name="stock", type="smallint", nullable=true)
     */
    private $stock = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_discontinued", type="boolean", nullable=false)
     */
    private $isDiscontinued = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_unlimited", type="boolean", nullable=false)
     */
    private $isUnlimited = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_available", type="boolean", nullable=false, options={"default"="1"})
     */
    private $isAvailable = '1';

    /**
     * @var int|null
     *
     * @ORM\Column(name="weight", type="smallint", nullable=true)
     */
    private $weight;

    /**
     * @var int|null
     *
     * @ORM\Column(name="rank", type="smallint", nullable=true)
     */
    private $rank;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cog", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $cog;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Válassz egy állapotot.")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer")
     */
    private $categoryId;

    /**
     *
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Válassz egy kategóriát.")
     */
    private $category;

//    /**
//     * @var int
//     *
//     * @ORM\Column(name="customer_id", type="integer")
//     */
//    private $customerId;
//
//    /**
//     * @var User
//     *
//     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="products")
//     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
//     */
//    private $customer;
//
//    /**
//     * One Product is in Many OrderItem.
//     *
//     * @var orderItems[]|Collection
//     * @OneToMany(targetEntity="App\Entity\OrderItem", mappedBy="product")
//     * // @ ORM\JoinColumn(name="item_id", referencedColumnName="product_id")
//     *
//     */
//    private $orderItems;


//    public function __construct()
//    {
//        $this->orderItems = new ArrayCollection();
//        $this->stock = 1;
//    }
//
//    /**
//     * @param Collection|OrderItem[] $orderItems
//     */
//    public function setOrderItems($orderItems)
//    {
//        $this->orderItems = $orderItems;
//    }
//
//    /**
//     * @return Collection|OrderItem[]
//     */
//    public function getOrderItems(): Collection
//    {
//        return $this->orderItems;
//    }



    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * @return \DateTime|null
     */

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return float
     */
    public function getGrossPrice()
    {
        return (float) $this->grossPrice;
    }

    /**
     * @return null|float
     */
    public function getNetPrice()
    {
        return (float) $this->netPrice;
    }

    /**
     * @return null|float
     */
    public function getPromoPrice()
    {
        return (float) $this->promoPrice;
    }

    /**
     * @return null|string
     */
    public function getCog()
    {
        return $this->cog;
    }

    /**
     * @return int|null
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return int|null
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return null|string
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * @return int|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return int|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     *
     */
    public function setCategoryId(int $categoryId):Product
    {
        $this->categoryId = $categoryId;
        
        //return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
    }

//    /**
//     * @return int
//     */
//    public function getCustomerId()
//    {
//        return $this->customerId;
//    }
//
//    /**
//     * @param int $customerId
//     *
//     */
//    public function setCustomerId(int $customerId)
//    {
//        $this->customerId = $customerId;
//        //return $this;
//    }
//
//    /**
//     * @return User
//     */
//    public function getCustomer()
//    {
//        return $this->customer->getFullName();
//    }
//
//    //* @return Product
//    /**
//     * @param User $customer
//     *
//     */
//    public function setCustomer(User $customer = null)
//    {
//        $this->customer = $customer;
//        //return $this;
//    }
//
//    /**
//     * @param Collection|OrderItem[] $orderItems
//     */
//    public function setOrderProducts($orderItems)
//    {
//        $this->orderItems = $orderItems;
//    }

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt()
    {
        $datum = new \DateTime();
        $datum->format('Y-m-d H:i:s');
        $this->createdAt = $datum;
    }

    /**
     * @param \DateTime|null $updatedAt
     */
    public function setUpdatedAt()
    {
        $datum = new \DateTime();
        $datum->format('Y-m-d H:i:s');
        $this->updatedAt = $datum;
    }

    /**
     * Set product name
     *
     * @param string $productName
     *
     * @return Product
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param float $grossPrice
     */
    public function setGrossPrice($grossPrice): ?float
    {
        $this->grossPrice = $grossPrice;
    }

    /**
     * @param null|float $netPrice
     */
    public function setNetPrice($netPrice): ?float
    {
        $this->netPrice = $netPrice;
    }

    /**
     * @param null|float $promoPrice
     */
    public function setPromoPrice($promoPrice): ?float
    {
        $this->promoPrice = $promoPrice;
    }

    /**
     * @param null|string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable)
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @param bool $isUnlimited
     */
    public function setIsUnlimited(bool $isUnlimited)
    {
        $this->isUnlimited = $isUnlimited;
    }

    /**
     * @param bool $isDiscontinued
     */
    public function setIsDiscontinued(bool $isDiscontinued)
    {
        $this->isDiscontinued = $isDiscontinued;
    }

    /**
     * @param int|null $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @param int|null $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @param int|null $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @param null|string $cog
     */
    public function setCog($cog)
    {
        $this->cog = $cog;
    }

    /**
     * @param null|string $thumb
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
    }

    /**
     * @param null|string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @param int|null $allapot
     */
    public function setStatus($allapot)
    {
        $this->status = $allapot;
    }


}
