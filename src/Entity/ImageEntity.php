<?php

namespace App\Entity;

//use ApiPlatform\Core\Annotation\ApiResource;
use App\Services\FileUploader;
use Doctrine\ORM\Mapping as ORM;
use Imagine\Gd\Image;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\ImageEntityRepository")
 * @ORM\Table(name="image")
 */

class ImageEntity implements JsonSerializable
{
    const PRODUCT_IMAGE = 'product';
    const STORE_IMAGE = 'store';

    /**
     * @var int
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string|null
     *
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    private $alt;
    /**
     * @var string
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="file", type="string", nullable=false)
     *
     * @Assert\NotBlank(message="Tölts fel egy képet.")
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg", "image/jpg" }, mimeTypesMessage="blabla")
     */
    private $file;

    /**
     * @var string
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     *
     * @Assert\NotBlank(message="Add meg a kép típusát.")
     */
    private $type;

    /**
     * @var string|null
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     */
    private $url;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'file'          => $this->getFile(),
        ];
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * The setId is required for the Serializer/Normalizer to be able to create
     * the subentities, and it must return the current entity !!
     *
     * @param int $id
     * @ return ImageEntity
     */
    public function setId(int $id)//: ImageEntity
    {
        $this->id = $id;
//        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getAlt(): ?string
    {
        return $this->alt;
    }
    /**
     * @param string $alt
     */
    public function setAlt(?string $alt)
    {
        $this->alt = $alt;
    }
    
    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
    
    /**
     * @param string $file
     */
    public function setFile(?string $file)
    {
        $this->file = $file;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns "store/image_filename.jpeg"
     * Constructs the relative path string.
     *
     * @return string
     */
    public function getPath(): ?string
    {
        if ($this->getFile()) {
            if ($this->type === self::STORE_IMAGE) {
                return FileUploader::WEBSITE_FOLDER_NAME.'/'.$this->getFile();
            }
            if ($this->type === self::PRODUCT_IMAGE) {
                return FileUploader::PRODUCTS_FOLDER_NAME.'/'.$this->getFile();
            }
        }
        return null;
    }

    /**
     * This is used in App\Event\ImageSetFullPath service/event. The service calls setImageUrl to set full URL to the image (eg: https://www....../image_filename.jpeg )
     * @param null|string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Return full URL: http://stulipan.dfr/media/cache/resolve/product_small/uploads/images/products/ethan-haddox-484912-unsplash-5ceea70235e84.jpeg
     * This is to be used API
     *
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}