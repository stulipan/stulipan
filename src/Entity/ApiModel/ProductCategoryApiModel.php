<?php

declare(strict_types=1);

namespace App\Entity\ApiModel;
use App\Entity\ImageEntity;
use App\Entity\Product\ProductCategory;

/**
 * Product
 *
 */
class ProductCategoryApiModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $slug;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var ProductCategory|null
     */
    public $parentCategory;

    /**
     * @var string|null
     */
    public $parentCategoryName;

    /**
     * @var bool
     */
    public $enabled;
    
    /**
     * @var ImageEntity|null
     */
    public $image;
    
    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @var string
     */
    private $urlToView;

    /**
     * @var string
     */
    private $urlToEdit;

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
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getParentCategory()
    {
        return $this->parentCategory;
    }
    
    /**
     * @param ProductCategory|null $parentCategory
     */
    public function setParentCategory($parentCategory)
    {
        $this->parentCategory = $parentCategory;
    }
    
    /**
     * @return null|string
     */
    public function getParentCategoryName()
    {
        return $this->parentCategoryName;
    }
    
    /**
     * @param null|string $parentCategoryName
     */
    public function setParentCategoryName($parentCategoryName)
    {
        $this->parentCategoryName = $parentCategoryName;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * @return ImageEntity|null
     */
    public function getImage()
    {
        return $this->image;
    }
    
    /**
     * @param ImageEntity|null $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
    
    /**
     * @return null|string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }
    
    /**
     * @param null|string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }
    
    /**
     * @return mixed
     */
    public function getUrlToEdit()
    {
        return $this->urlToEdit;
    }

    /**
     * @param mixed $url
     */
    public function setUrlToEdit($url)
    {
        $this->urlToEdit = $url;
    }

    /**
     * @return string
     */
    public function getUrlToView(): string
    {
        return $this->urlToView;
    }

    /**
     * @param mixed $url
     */
    public function setUrlToView(string $url)
    {
        $this->urlToView = $url;
    }




}
