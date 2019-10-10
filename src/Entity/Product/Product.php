<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Product\ProductCategory;
use App\Entity\Price;
use App\Entity\TimestampableTrait;
use App\Services\FileUploader;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 * )
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @UniqueEntity("sku", message="Ez az SKU kód már használatban!")
 */
class Product //implements \JsonSerializable
{
    const ENABLED = 1;
    const UNAVAILABLE = 2;
    const REMOVED = 3;
    
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @ORM\Column(name="id", type="smallint", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @Assert\NotBlank(message="Adj nevet a terméknek.")
     * @ORM\Column(name="product_name", type="string", length=100, nullable=true)
     */
    private $name;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @Assert\NotBlank(message="Nem adtál meg SKU-t.")
     * @ORM\Column(name="sku", type="string", length=20, nullable=false)
     */
    private $sku;

//    /**
//     * @ var float
//     * @Assert\NotBlank()
//     * @Assert\Range(min=0, minMessage="Az összeg nem lehet negatív.")
//     * @ORM\Column(name="gross_price", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
//     */
//    private $grossPrice = 0;

    /**
     * @var Price
     * @Groups({"productView", "productList"})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Price", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=false)
     * @Assert\Type(type="App\Entity\Price")
     * @Assert\Valid
     * @ Assert\NotNull(message="Adj árat a terméknek.")
     */
    private $price;

//    /**
//     * @var string|null   --- Ez mar NINCS HASZNALATBAN ---
//     *
//     * @ORM\Column(name="image", type="string", length=1000, nullable=true)
//     * @Assert\File(mimeTypes={ "image/png", "image/jpeg" }, groups = {"create"})
//     */
//    private $image;
    
    /**
     * @ApiSubresource()
     *
     * @var ProductImage[]|ArrayCollection|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * ==== One Product has many product Images ====
     * ==== mappedBy="order" => az OrderItem entitásban definiált 'order' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="ProductImage", mappedBy="product", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="product_id", nullable=false)
     * @ Assert\NotBlank(message="Egy terméknek legalább egy kép szükséges.")
     */
    private $images;

    /**
     * @var int|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @Assert\NotBlank()
     * @Assert\Range(min=0, max="1000000", minMessage="Nem lehet negatív.")
     * @ORM\Column(name="stock", type="smallint", nullable=true)
     */
    private $stock;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_discontinued", type="boolean", nullable=false, )
     */
    private $isDiscontinued = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_unlimited", type="boolean", nullable=false, options={"default":0})
     */
    private $isUnlimited=0;
    
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
     * @var ProductStatus
     * @Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductStatus", cascade={"persist"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy állapotot.")
     */
    private $status;

//    /**
//     * @var int
//     *
//     * @ORM\Column(name="category_id", type="integer")
//     */
//    private $categoryId;
 
    /**
     * @ApiSubresource()
     *
     * @var ProductCategory[]|ArrayCollection|null
     * @Groups({"productView"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Product\ProductCategory", inversedBy="products")
     * @ORM\JoinTable(name="product_selected_categories",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"name": "ASC"})
     * @Assert\NotBlank(message="Válassz kategóriát.")
     */
    private $categories;
    
    /**
     * @ApiSubresource()
     *
     * @var ProductBadge[]|ArrayCollection|null
     * @Groups({"productView"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Product\ProductBadge", inversedBy="products")
     * @ORM\JoinTable(name="product_selected_badges",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="badge_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"ordering": "ASC"})
     * @Assert\NotBlank(message="Válassz matricát.")
     */
    private $badges;

    /**
     * @ApiSubresource()
     *
     * @var ProductKind|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductKind", inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(name="kind_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy terméktípust.")
     */
    private $kind;

    /**
     * @var Product|null
     * @Groups({"productView"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\Product", inversedBy="subproducts")
     * @ORM\JoinColumn(name="parent_product_id", referencedColumnName="id", nullable=true)
     * Assert\NotBlank(message="Legalább egy apatermék kell legyen.")
     */
    private $parent;

    /**
     * @var ProductAttribute|null
     *
     * ==== One Subproduct is one Attribute => Egy altermék mindig egy attribútum ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Product\ProductAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=true)
     * Assert\NotBlank(message="Az altermék egy attribútum kell legyen.")
     */
    private $attribute;

    /**
     * @var Product[]|ArrayCollection|null
     * @Groups({"productView"})
     *
     * ==== One Product has Subproducts ====
     * ==== mappedBy="product" => a Subproduct entitásban definiált 'product'-ról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product\Product", mappedBy="parent")
     * @ORM\JoinColumn(name="id", referencedColumnName="parent_product_id", nullable=true)
     * @ORM\OrderBy({"attribute" = "ASC"})   ///// >????????
     * @Assert\NotBlank(message="Egy terméknek több alterméke lehet.")
     */
    private $subproducts;
    
    //////////////////////////////////////////////////////////////////////////////////////////
    ///
    ///   VIRTUAL PROPERTIES
    ///
    //////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @var string|null
     * @Groups({"productView"})
     */
    private $backToList;
    
//    /**
//     * @var Collection
//     *
//     * ==== One Product has Subproducts ====
//     * ==== mappedBy="product" => a Subproduct entitásban definiált 'product'-ról van szó ====
//     *
//     * @ORM\OneToMany(targetEntity="App\Entity\Subproduct", mappedBy="product", orphanRemoval=true, cascade={"persist"})
//     * @ORM\JoinColumn(name="id", referencedColumnName="product_id", nullable=true)
//     * @ORM\OrderBy({"attribute" = "ASC"})
//     * @Assert\NotBlank(message="Egy terméknek több alterméke lehet.")
//     */
//    private $subproducts;

//    /**
//     * @var nincs
//     *
//     * @ Assert\NotBlank(message="Válassz egy terméktípust.")
//     */
//    private $selectedSubproduct;

    public function __construct()
    {
        $this->subproducts = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->badges = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'kind'          => $this->getKind(),
            'sku'           => $this->getSku(),
            'description'   => $this->getDescription(),
            'status'        => $this->getStatus(),
            'price'         => $this->getPrice(),
            'stock'         => $this->getStock(),
            'categories'    => $this->getCategories(),
//            'image'         => $this->getImage(),
            'attribute'     => $this->getAttributeName(),
        ];
    }
    
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * The setId is required for the Serializer/Normalizer to be able to create
     * the subentities, and it must return the current entity !!
     *
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    /**
     * Kompatibilitas miatt mert tele van a twig templatek ezzel...
     * @return null|string
     */
    public function getProductName(): ?string
    {
        return $this->name;
    }
    
    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
//    /**
//     * @param null|float $grossPrice
//     */
//    public function setGrossPrice(?float $grossPrice): void
//    {
//        $this->grossPrice = $grossPrice;
//    }
//
//    /**
//     * @return float
//     */
//    public function getGrossPrice()
//    {
//        return (float) $this->grossPrice;
//    }

    /**
     * @return Price
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    /**
     * @param Price $price
     */
    public function setPrice(?Price $price)
    {
        $this->price = $price;
    }
    
    /**
     * @return ProductImage[]|Collection
     */
    public function getImages(): Collection
    {
        return $this->images;
    }
    
    public function addImage(ProductImage $image): void
    {
        if (!$this->images->contains($image)) {
            $image->setProduct($this);
            $this->images->add($image);
        }
    }
    public function removeImage(ProductImage $image): void
    {
        $image->setProduct(null);
        $this->images->removeElement($image);
    }
    
    /**
     * Returns the main ProductImage (ordering=0)
     * @Groups({"orderView"})
     *
     * @return null|string
     */
    public function getCoverImage()
    {
        if (!$this->images->isEmpty()) {
            foreach ($this->images as $image) {
                if ($image->getOrdering() === 0) {
//                    return $image->getImageUrl();
                    return FileUploader::PRODUCT_FOLDER . '/' . $image->getImage()->getFile();
                }
            }
        }
        return '';
    }
    
//    /**
//     * Ez a regi mikor a kep nem volt kulon ProductImage entitasban
//     */
//    public function getImagePath()
//    {
//        if ($this->getImage()) {
//            return FileUploader::PRODUCT_FOLDER . '/' . $this->getImage();
//        }
//    }

    /**
     * @return null|string
     */
    public function getCog()
    {
        return $this->cog;
    }
    
    /**
     * @param null|string $cog
     */
    public function setCog($cog)
    {
        $this->cog = $cog;
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

    /**
     * @return int|null
     */
    public function getRank()
    {
        return $this->rank;
    }
    
    /**
     * @param int|null $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return int|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return ProductStatus|null
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @param int|null $allapot
     */
    public function setStatus($allapot)
    {
        $this->status = $allapot;
    }
    
    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status->getId() ? true : false;
    }
    
    /**
     * @return bool
     */
    public function isUnavailable(): bool
    {
        return self::UNAVAILABLE === $this->status->getId() ? true : false;
    }
    
    /**
     * @return bool
     */
    public function isRemoved(): bool
    {
        return self::REMOVED === $this->status->getId() ? true : false;
    }
    
    /**
     * @ return ProductCategory[]|Collection
     */
    public function getCategories() //: Collection
    {
        return $this->categories->getValues();
    }
    
    public function addCategory(ProductCategory $category): void
    {
        if (!$this->categories->contains($category)) {
//            $category->setProduct($this);
            $this->categories->add($category);
        }
    }
    
    public function removeCategory(ProductCategory $category): void
    {
        $this->categories->removeElement($category);
    }
    
    
    /**
     * @return bool
     */
    public function hasBadges(): bool
    {
        return $this->badges->isEmpty() ? false : true;
    }
    
    /**
     * @ return ProductBadge[]|Collection
     */
    public function getBadges() //: Collection
    {
        return $this->badges->getValues();
    }
    
    public function addBadge(ProductBadge $badge): void
    {
        if (!$this->badges->contains($badge)) {
//            $category->setProduct($this);
            $this->badges->add($badge);
        }
    }
    
    public function removeBadge(ProductBadge $badge): void
    {
        $this->badges->removeElement($badge);
    }

    /**
     * @return ProductKind
     */
    public function getKind(): ?ProductKind
    {
        return $this->kind;
    }

    /**
     * @param ProductKind $kind
     *
     */
    public function setKind(?ProductKind $kind): void
    {
        $this->kind = $kind;
    }
    
    
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @param null|string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @param int|null $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
    
    /**
     * @param bool $isUnlimited
     */
    public function setIsUnlimited(bool $isUnlimited)
    {
        $this->isUnlimited = $isUnlimited;
    }
    
    /**
     * @return Product
     */
    public function getParent(): ?Product
    {
        return $this->parent;
    }
    
    /**
     * @param Product $parentProduct
     */
    public function setParent(?Product $parentProduct)
    {
        $this->parent = $parentProduct;
    }
    
    /**
     * @return ProductAttribute|null
     */
    public function getAttribute(): ?ProductAttribute
    {
        return $this->attribute;
    }
    
    /**
     * @param ProductAttribute $attribute
     */
    public function setAttribute(?ProductAttribute $attribute)
    {
        $this->attribute = $attribute;
    }
    
    /**
     * @return string|null
     */
    public function getAttributeName(): ?string
    {
        return $this->getAttribute() ? $this->getAttribute()->getName() : '';
    }

    /**
     * @param Product $item
     */
    public function addSubproduct(Product $item): void
    {
        if (!$this->subproducts->contains($item)) {
            $item->setParent($this);
            $this->subproducts->add($item);
        }
    }

    /**
     * @param Product $item
     */
    public function removeSubproduct(Product $item): void
    {
        $item->setParent(null);
        $this->subproducts->removeElement($item);
    }

    /**
     * @return Product[]|Collection
     */
    public function getSubproducts(): Collection
    {
        return $this->subproducts;
    }

    /**
     * @return bool
     */
    public function hasSubproducts(): bool
    {
        return $this->getSubproducts()->isEmpty() ? false : true;
    }

    /**
     * Returns the Subproduct containing the Attribute
     * @return Product $subproduct
     */
    public function getSubproductWithAttribute(ProductAttribute $attribute)
    {
        foreach ($this->subproducts as $i => $subproduct) {
            if ($subproduct->getAttribute() == $attribute) {
                return $subproduct;
            }
        }
    }
    
    /**
     * @param null|string $backToList
     */
    public function setBackToList($backToList)
    {
        $this->backToList = $backToList;
    }
    
    /**
     * @return null|string
     */
    public function getBackToList()
    {
        return $this->backToList;
    }
    
    
    

//    /**
//     * @param Product $subproduct
//     */
//    public function setSelectedSubproduct(?Product $subproduct)
//    {
//        $this->selectedSubproduct = $subproduct;
//    }
//
//    /**
//     * @return Product
//     */
//    public function getSelectedSubproduct(): ?Product
//    {
//        return $this->selectedSubproduct;
//    }

}
