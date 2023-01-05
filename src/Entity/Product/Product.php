<?php

declare(strict_types=1);

namespace App\Entity\Product;

//use ApiPlatform\Core\Annotation\ApiResource;

use App\Entity\Product\ProductCategory;
use App\Entity\Price;
use App\Entity\SalesChannel;
use App\Entity\TimestampableTrait;
use App\Services\FileUploader;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * ORM\EntityListeners({"App\Event\SetSlugProduct"})
 * @UniqueEntity("sku", message="product.sku-already-in-use")
 * @UniqueEntity("slug", message="product.slug-already-in-use")
 */
class Product
{
    // Ezeket mar nem hasznalom
    const STATUS_ENABLED = 1;
    const STATUS_UNAVAILABLE = 2;
    const STATUS_REMOVED = 3;
    
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
     * @Groups({"productView", "productList", "eventAddToCart",
     *     "orderView"})
     *
     * @Assert\NotBlank(message="product.name-is-missing")
     * @ORM\Column(name="product_name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     * @Groups({"productView", "productList", "eventAddToCart",
     *     "orderView"})
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=false, unique=true)
     * @ Assert\NotBlank(message="A slug nem lehet üres. Pl: ez-egy-termek")
     *      --> The slug is created in 2 steps with SetSlugProduct event:
     *      --> First: a temporary slug on prePersist, which is then replaced with the permanent one upon postPersist
     *      --> Therefore the NotBlank validation isn't required.
     */
    private $slug;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList", "eventAddToCart",
     *     "orderView"})
     *
     * @Assert\NotBlank(message="product.sku-is-missing")
     * @ORM\Column(name="sku", type="string", length=100, nullable=false)
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
     * @var Price|null
     * @Groups({"productView", "productList", "eventAddToCart"})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Price", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=false)
     * @Assert\Type(type="App\Entity\Price")
     * @Assert\Valid
     * @CustomAssert\PriceExist(message="product.price-is-missing")
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
     * ()
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
     * @ORM\OrderBy({"ordering": "ASC"})
     * Assert\NotBlank(message="Egy terméknek legalább egy kép szükséges.")
     */
    private $images;

    /**
     * @var int|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @Assert\NotBlank(message="product.stock-is-missing")
     * @Assert\Range(min=0, max="1000000", minMessage="product.stock-is-negative")
     * @ORM\Column(name="stock", type="smallint", nullable=true)
     */
    private $stock;

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
     * @var bool
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="is_flower", type="boolean", nullable=false, options={"default"=false})
     */
    private $flower = false;

    /**
     * @var ProductStatus
     * @Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductStatus", cascade={"persist"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="product.status-is-missing")
     */
    private $status;

//    /**
//     * @var int
//     *
//     * @ORM\Column(name="category_id", type="integer")
//     */
//    private $categoryId;
 
    /**
     * ()
     *
     * @var ProductCategory[]|ArrayCollection|null
     * @Groups({"productView", "eventAddToCart"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Product\ProductCategory", inversedBy="products")
     * @ORM\JoinTable(name="product_selected_categories",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"name": "ASC"})
     * @Assert\NotBlank(message="product.collection-is-missing")
     * @Assert\Count(min = "1", minMessage = "product.collection-is-missing")
     */
    private $categories;
    
    /**
     * ()
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
     * Assert\NotBlank(message="Válassz matricát.")
     */
    private $badges;

    /**
     * ()
     *
     * @var ProductOption[]|ArrayCollection|null
     * @Groups({"productView"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductOption", mappedBy="product", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="product_id")
     * @ORM\OrderBy({"position" = "ASC"})
     * @Assert\NotBlank(message="Válassz optionokat.")
     */
    private $options;

    /**
     * ()
     *
     * @var ProductVariant[]|ArrayCollection|null
     * @Groups({"productView"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductVariant", mappedBy="product", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="product_id")
     * @ORM\OrderBy({"position" = "ASC"})
     * Assert\NotBlank(message="Válassz variansokat.")
     */
    private $variants;

    /** @var bool
     *  @Groups({"productView"})
     */
    private $hasVariants;

    /**
     * ()
     *
     * @var ProductKind|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductKind", inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(name="kind_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(message="product.type-is-missing")
     */
    private $kind;

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
     * @var SalesChannel[]|ArrayCollection|null
     * @Groups({"productView"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\SalesChannel", inversedBy="products")
     * @ORM\JoinTable(name="product_selected_sales_channels",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="sales_channel_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"ordering": "ASC"})
     */
    private $salesChannels;
    
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

    /**
     * @var string|null
     * @Groups({
     *     "productView",
     *     "productList"
     * })
     */
    private $json;

//    private $sellingPrice;
//    private $compareAtPrice;


    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->hasVariants =  $this->hasVariants();
        $this->salesChannels = new ArrayCollection();
    }
    
//    /**
//     * {@inheritdoc}
//     */
//    function jsonSerialize()
//    {
//        return [
//            'id'            => $this->getId(),
//            'name'          => $this->getName(),
//            'slug'          => $this->getSlug(),
//            'kind'          => $this->getKind(),
//            'sku'           => $this->getSku(),
//            'description'   => $this->getDescription(),
//            'status'        => $this->getStatus(),
//            'price'         => $this->getPrice(),
//            'sellingPrice'  => $this->getSellingPrice(),
//            'compareAtPrice' => $this->getCompareAtPrice(),
//            'stock'         => $this->getStock(),
//            'categories'    => $this->getCategories(),
////            'image'         => $this->getImage(),
//            'attribute'     => $this->getAttributeName(),
//        ];
//    }
    
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
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
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
     * @param Price|null $price
     */
    public function setPrice(?Price $price)
    {
        $this->price = $price;
    }

    /**
     * @Groups({
     *     "productView",
     *     "productList"
     * })
     *
     * @return float|null
     */
    public function getSellingPrice()
    {
        return $this->getPrice()->getNumericValue();
    }

    /**
     * @Groups({
     *     "productView",
     *     "productList",
     * })
     *
     * @return float|null
     */
    public function getCompareAtPrice()
    {
        return $this->getPrice()->getCompareAtValue();
    }

    public function getDiscountPercentage()
    {
        if ($this->getCompareAtPrice() != null) {
            $discount = (int) floor( (1 - $this->getSellingPrice()/$this->getCompareAtPrice() ) * 100 );
            return sprintf('-%s%%', $discount);
        }
        return 0;
    }

    public function getDiscountAmount()
    {
        if ($this->isOnSale()) {
            return $this->getCompareAtPrice()-$this->getSellingPrice();
        }
        return 0;
    }

    public function isOnSale()
    {
        return null === $this->getCompareAtPrice() ? false : true;
    }

    /**
     * @Groups({"productView", "productList",
     *     "orderView"})
     */
    public function getCoverImage()
    {
//        return $this->getImages()->first();
        return $this->getImages()[0];
    }

    /**
     * @ return ProductImage[]|Collection
     */
    public function getImages()//: Collection
    {
        return $this->images ? $this->images->getValues(): $this->images;
//        return $this->images->getValues();
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
    public function getCoverImageAsset()
    {
        if (!$this->images->isEmpty()) {
            foreach ($this->images as $image) {
                if ($image->getOrdering() === 0) {
                    return FileUploader::PRODUCTS_FOLDER_NAME . '/' . $image->getImage()->getFile();
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
//            return FileUploader::PRODUCTS_FOLDER_NAME . '/' . $this->getImage();
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

//    /**
//     * @return bool
//     */
//    public function getFlower(): bool
//    {
//        return null === $this->flower ? false : $this->flower;
//    }

    /**
     * Returns true or false, after transformation (1 or 0 which are stored in db)
     * @return bool
     */
    public function isFlower(): bool
    {
        return null === $this->flower ? false : $this->flower;
    }

    /**
     * Sets value to 1 or 0 which are stored in db
     * @param bool $flower
     */
    public function setFlower(?bool $flower)
    {
//        $this->enabled = true === $enabled ? 1 : 0;
        $this->flower = $flower;
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
    public function isPubliclyAvailable(): bool
    {
        if ($this->status->getShortcode() != ProductStatus::STATUS_UNAVAILABLE && $this->status->getShortcode() != ProductStatus::STATUS_REMOVED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $quantity
     * @return bool
     */
    public function hasEnoughStock(int $quantity): bool
    {
        if ($this->stock - $quantity < 0) {
            return false;
        }
        return true;
    }
    
    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return ProductStatus::STATUS_ENABLED === $this->status->getShortcode() ? true : false;
    }
    
    /**
     * @return bool
     */
    public function isUnavailable(): bool
    {
        return ProductStatus::STATUS_UNAVAILABLE === $this->status->getShortcode() ? true : false;
    }
    
    /**
     * @return bool
     */
    public function isRemoved(): bool
    {
        return ProductStatus::STATUS_REMOVED === $this->status->getShortcode() ? true : false;
    }

    /**
     * @ return ProductCategory[]|Collection
     */
    public function getCategories() //: Collection
    {
        return $this->categories ? $this->categories->getValues(): $this->categories;
//        return $this->categories->getValues();
    }
    
    public function addCategory(ProductCategory $category): void
    {
        if (!$this->categories->contains($category)) {
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
        return $this->badges ? $this->badges->getValues(): $this->badges;
//        return $this->badges->getValues();
    }
    
    public function addBadge(ProductBadge $badge): void
    {
        if (!$this->badges->contains($badge)) {
            $this->badges->add($badge);
        }
    }
    
    public function removeBadge(ProductBadge $badge): void
    {
        $this->badges->removeElement($badge);
    }

    /**
     * @ return ProductOption[]|Collection
     */
    public function getOptions()//: Collection
    {
        $criteria = Criteria::create()->orderBy(['position' => Criteria::ASC]);
        $options = $this->options->matching($criteria);

        return $options ? $options->getValues(): $options;
//        return $options->getValues();
    }

    public function addOption(ProductOption $option): void
    {
        if (!$this->options->contains($option)) {
            $option->setProduct($this);
            $this->options->add($option);
        }
    }

    public function removeOption(ProductOption $option): void
    {
        $this->options->removeElement($option);
    }

    /**
     * @return bool
     */
    public function hasOptions(): bool
    {
        return $this->options->isEmpty() ? false : true;
    }

    /**
     * @ return ProductVariant[]|Collection
     */
    public function getVariants()
    {
        return $this->variants ? $this->variants->getValues(): $this->variants;
    }

    /**
     * Returns the Variant for the given option and option value
     * @return ProductVariant|null
     */
    public function getVariant(ProductOption $option, ProductOptionValue $value)
    {
//        $criteria = Criteria::create()->where(Criteria::expr()->contains("options", $option));
//        $dem = $this->variants->filter($criteria);

        $variants = new ArrayCollection();
        foreach ($this->variants as $variant) {
            if ($variant->getOptions()->contains($option)) {
                if ($variant->getOptions())
//                $variants[] = $variant;
                $variants->add($variant);
            }
        }

        if ($variants->isEmpty()) {
            return null;
        }
        return $variants;
    }



    public function addVariant(ProductVariant $item): void
    {
        if (!$this->variants->contains($item)) {
            $item->setProduct($this);
            $this->variants->add($item);
        }
    }

    public function removeVariant(ProductVariant $item): void
    {
        $this->variants->removeElement($item);
    }

    /**
     * @return bool
     */
    public function hasVariants(): bool
    {
        return $this->variants
            ? $this->variants->isEmpty() ? false : true
            : false ;
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
     * Finds a ProductOption by its name
     * $criteria array must have a key called 'name'. Eg: ['name' => 'Size']
     *
     * @param array $criteria
     * @return ProductOption|null
     */
    public function findOptionBy($criteria = []): ?ProductOption
    {
        if (isset($criteria['name'])) {
            return $option = $this->options->filter(
                function ($item) use ($criteria) {
                    return $item->getName() === $criteria['name'] ? $item : null;
                }
            )->first();
        }
        return null;
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
     * @return bool
     */
    public function hasSalesChannel(): bool
    {
        return $this->salesChannels->isEmpty() ? false : true;
    }

    /**
     * @return SalesChannel[]|Collection
     */
    public function getSalesChannels()
    {
        return $this->salesChannels ? $this->salesChannels->getValues(): $this->salesChannels;
    }

    public function addSalesChannel(SalesChannel $item): void
    {
        if (!$this->salesChannels->contains($item)) {
            $this->salesChannels->add($item);
        }
    }

    public function removeSalesChannel(SalesChannel $item): void
    {
        $this->salesChannels->removeElement($item);
    }

    /**
     * @param SalesChannel[]|ArrayCollection|null $salesChannels
     */
    public function setSalesChannels($salesChannels): void
    {
        $this->salesChannels = $salesChannels;
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

    /**
     * @return string|null
     */
    public function getJson(): ?string
    {
        return $this->json;
    }

    /**
     * @param string|null $json
     */
    public function setJson(?string $json): void
    {
        $this->json = $json;
    }
}
