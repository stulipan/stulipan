<?php

namespace App\Entity\Product;
//use ApiPlatform\Core\Annotation\ApiResource;

use App\Entity\ImageEntity;
use App\Services\FileUploader;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 *
 * @ORM\Entity
 * @ORM\Table(name="product_image")
 * @ ORM\Entity(repositoryClass="App\Entity\Product\Repository\ProductAttributeRepository")
 */
class ProductImage //implements \JsonSerializable
{
    /**
     * @var int
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @ORM\Column(name="id", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Product
     *
     * ==== Many Images in one Product ====
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="images")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Rendelj legalább egy képet a termékhez.")
     */
    private $product;
    
    /**
     * @var ImageEntity
     *
     * ==== One ProductImage is one ImageEntity => Egy termékkép mindig egy sima kép lesz ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ImageEntity")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="A termékkép egy sima kép kell legyen.")
     */
    private $image;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     */
    private $imageUrl;

    /**
     * @var string|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     */
    private $thumbnailUrl;


    /**
     * @var string
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    private $alt;
    
    /**
     * @var int
     * @Groups({"productView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", nullable=false, options={"default"="100"})
     */
    private $ordering;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'alt'           => $this->getAlt(),
            'product'       => $this->getProduct(),
            'image'         => $this->getImage(),
        ];
    }
    
    private $container;
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __toString(): string
    {
        return $this->image->getFile();
    }
    
    /**
     * @return string|null
     */
    public function getAlt(): ?string
    {
        return $this->alt;
    }
    
    /**
     * @param string|null $alt
     */
    public function setAlt(?string $alt)
    {
        $this->alt = $alt;
    }
    
    /**
     * @return Product
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }
    
    /**
     * @param Product $product
     */
    public function setProduct(?Product $product)
    {
        $this->product = $product;
    }
    
    /**
     * @return ImageEntity
     */
    public function getImage(): ImageEntity
    {
        return $this->image;
    }
    
    /**
     * @param ImageEntity $image
     */
    public function setImage(ImageEntity $image)
    {
        $this->image = $image;
    }
    
    /**
     * This is used in App\Event\ImageSetFullPath service/event. The service calls setImageUrl to set full URL to the image (eg: https://www....../image_filename.jpeg )
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
     * @return null|string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return string|null
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    /**
     * @param string|null $thumbnailUrl
     */
    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    
    /**
     * Returns "products/image_filename.jpeg"
     * This is to be used in Twig templates with uploaded_asset()
     *
     * @return string
     */
    public function getImagePath(): ?string
    {
        if ($this->getImage()) {
            return FileUploader::PRODUCTS_FOLDER_NAME.'/'.$this->getImage()->getFile();
        }
        return null;
    }


    /**
     * @return int
     */
    public function getOrdering(): ?int
    {
        return $this->ordering;
    }
    
    /**
     * @param mixed $ordering
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }
    
}