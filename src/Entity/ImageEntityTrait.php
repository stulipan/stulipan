<?php

declare(strict_types=1);

namespace App\Entity;

use App\Services\FileUploader;
use Doctrine\ORM\Mapping as ORM;

trait ImageEntityTrait
{

    /**
     * @var ImageEntity|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ImageEntity", cascade={"persist"}) // No need for cascade={"persist"} as the ImageEntity will previously be saved to db
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     **/
    private $image;

    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @return ImageEntity
     */
    public function getImage(): ?ImageEntity
    {
        return $this->image;
    }

    /**
     * @param ImageEntity|null $image
     */
    public function setImage(?ImageEntity $image): void
    {
        $this->image = $image;
//        return $this;
    }

    /**
     * This is used in ImageSetFullPath service. The service calls setImageUrl to set full URL to the image (eg: https://www....../image_filename.jpeg )
     * @param null|string $imageUrl
     */
    public function setImageUrl(?string $imageUrl)
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
     * Returns "products/image_filename.jpeg"
     * This is to be used in Twig templates with uploaded_asset()
     *
     * @return string|null
     */
    public function getImagePath(): ?string
    {
        if ($this->getImage()) {
            return FileUploader::WEBSITE_FOLDER_NAME .'/'. $this->getImage()->getFile();
        }
        return null;
    }
}