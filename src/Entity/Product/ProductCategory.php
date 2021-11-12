<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\Common\Collections\Criteria;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\ImageEntity;
use App\Services\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductCategoryRepository")
 * @ORM\Table(name="product_category")
 * @UniqueEntity("slug", message="collection.slug-already-in-use")
 */
class ProductCategory implements JsonSerializable
{
    /**
     * @var int
     * @Groups({
     *     "main",
     *     "productView"
     * })
     *
     * @ORM\Column(type="smallint", name="id", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({
     *     "main",
     *     "productView",
     *     "eventAddToCart",
     * })
     *
     * @ORM\Column(name="category_name", type="string", length=100, nullable=false)
     * @Assert\NotNull(message="collection.name-is-missing")
     */
    private $name;

    /**
     * @var string
     * @Groups({"main"})
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=false, unique=true)
     * @Assert\NotBlank(message="collection.slug-is-missing")
     * @ Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @var string|null
     * @Groups({"main"})
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="seo_content", type="text", length=65535, nullable=true)
     */
    private $seoContent;
    
    /**
     * @var ImageEntity|null
     * @Groups({"main"})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ImageEntity", cascade={"persist"}) // No need for cascade={"persist"} as the ImageEntity will previously be saved to db
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     **/
    private $image;
    
    /**
     * @var string|null
     * @Groups({"main"})
     */
    private $imageUrl;

    /**
     * @var ProductCategory|null
     * @Groups({"main"})
     *
     * ==== One parent is a Category ====
     *
     * @ MaxDepth(1)
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductCategory", inversedBy="subcategories", cascade={"persist"}) //
     * @ORM\JoinColumn(name="parent_category_id", referencedColumnName="id") //, nullable=true
     * Assert\NotBlank(message="Legalább egy apa kategória kell legyen.")
     */
    private $parent;

    /**
     * @var ProductCategory[]|ArrayCollection|null
     * @Groups({"main"})
     *
     * ==== One Category may have Subcategories ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductCategory", mappedBy="parent")
     * @ORM\JoinColumn(name="id", referencedColumnName="parent_category_id", nullable=true)
     * @ORM\OrderBy({"name" = "ASC"})
     * @Assert\NotBlank(message="Egy kategóriának több alkategóriája lehet.")
     */
    private $subcategories;

    /**
     * @var bool
     * @Groups({"main"})
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=false})
     */
    private $enabled;

//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
//     *
//     */
//    private $products;
    
    /**
     * @var Product[] | ArrayCollection | null
     *
     *
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="categories")
     * @ORM\OrderBy({"rank"="ASC"})
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->subcategories = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'slug'              => $this->getSlug(),
            'description'       => $this->getDescription(),
            'parent'            => $this->getParent(),
            'enabled'           => $this->getEnabled(),
            'image'             => $this->getImage(),
            'imageUrl'          => $this->getImageUrl(),
//            'subcategories'     => $this->getSubcategories(),
//            'products'          => $this->getProducts(),

        ];
    }

    /**
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
     * @return ProductCategory
     */
    public function setId(int $id): ProductCategory
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * @param string $nev
     * @return ProductCategory
     */
    public function setName($nev): ProductCategory
    {
        $this->name = $nev;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
    
    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
    
    /**
     * @param string $slug
     * @return ProductCategory
     */
    public function setSlug(string $slug): ProductCategory
    {
        $this->slug = $slug;
        return $this;
    }

    
    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ProductCategory
     */
    public function setDescription(?string $description): ProductCategory
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoContent(): ?string
    {
        return $this->seoContent;
    }

    /**
     * @param string|null $seoContent
     */
    public function setSeoContent(?string $seoContent): void
    {
        $this->seoContent = $seoContent;
    }

    /**
     * @return ImageEntity
     */
    public function getImage(): ?ImageEntity
    {
        return $this->image;
    }
    
    /**
     * @param ImageEntity $image
     * @return ProductCategory|null
     */
    public function setImage(?ImageEntity $image): ?ProductCategory
    {
        $this->image = $image;
        return $this;
    }
    
    /**
     * This is used in ImageSetFullPath service. The service calls setImageUrl to set full URL to the image (eg: https://www....../image_filename.jpeg )
     * @param null|string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }
    
    /**
     * Return full URL: http://stulipan.dfr/media/cache/resolve/product_small/uploads/images/products/ethan-haddox-484912-unsplash-5ceea70235e84.jpeg
     * This is to be used API
     *
     *      This is generated in the ImageSetFullPath.php event (!!)
     *
     * @return null|string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
    
    /**
     * Returns "store/image_filename.jpeg"
     * This is to be used in Twig templates with uploaded_asset()
     *
     * @return string
     */
    public function getImagePath(): ?string
    {
        if ($this->getImage()) {
            return FileUploader::WEBSITE_FOLDER_NAME.'/'.$this->getImage()->getFile();
        }
        return null;
    }
    
    
//    /**
//     * @param string $imageUrl
//     */
//    public function setImageUrl(string $imageUrl)
//    {
//        $this->imageUrl = $imageUrl;
//    }
    
    

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
//        return 1 !== $this->enabled ? false : true;
        return null === $this->enabled ? false : $this->enabled;
    }

    /**
     * Returns true or false, after transformation (1 or 0 which are stored in db)
     * @return bool
     */
    public function isEnabled(): bool
    {
//        return 1 !== $this->enabled ? false : true;
        return null === $this->enabled ? false : $this->enabled;
    }

    /**
     * Sets value to 1 or 0 which are stored in db
     * @param bool $enabled
     */
    public function setEnabled(?bool $enabled)
    {
//        $this->enabled = true === $enabled ? 1 : 0;
        $this->enabled = $enabled;
    }

    /**
     * @return ProductCategory|null
     */
    public function getParent(): ?ProductCategory
    {
        return $this->parent;
    }

    /**
     * @param ProductCategory|null $parent
     */
    public function setParent(?ProductCategory $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param ProductCategory $item
     */
    public function addSubcategory(ProductCategory $item)
    {
        if (!$this->subcategories->contains($item)) {
            $item->setParent($this);
            $this->subcategories->add($item);
        }
    }

    /**
     * @param ProductCategory $item
     */
    public function removeSubcategory(ProductCategory $item)
    {
        $item->setParent(null);
        $this->subcategories->removeElement($item);
    }

    /**
     * @return ProductCategory[]|Collection|null
     */
    public function getSubcategories(): ?Collection
    {
        return $this->subcategories->isEmpty() ? null : $this->subcategories;
    }

    /**
     * @return Product[]|Collection|null
     */
    public function getProducts(): ?Collection
    {
        return $this->products->isEmpty() ? null : $this->products;
    }

    /**
     * @param Product $item
     */
    public function addProduct(Product $item)
    {
        if (!$this->products->contains($item)) {
            $item->addCategory($this);
            $this->products->add($item);
        }
    }
    
    /**
     * @param Product $item
     */
    public function removeProduct(Product $item)
    {
        $item->removeCategory($this);
        $this->products->removeElement($item);
    }
}