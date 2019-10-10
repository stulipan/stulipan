<?php

declare(strict_types=1);

namespace App\Entity\ApiModel;

/**
 * Product
 *
 */
class ProductApiModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string|null
     */
    public $sku;

    /**
     * @var string|null
     */
    public $productName;

    /**
     * @var float
     */
    private $price;

//    /**
//     * @var ProductPrice
//     *
//     * @ORM\OneToOne(targetEntity="App\Entity\ProductPrice", inversedBy="product")
//     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=true)
//     * @Assert\Type(type="App\Entity\ProductPrice")
//     * @Assert\Valid
//     */
//    private $price;

    /**
     * @var string|null
     *
     */
    private $image;

    /**
     * @var int|null
     */
    private $stock;

    /**
     * @var string
     *
     */
    private $status;

    /**
     * @var string
     */
    private $url;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice(?float $price)
    {
        $this->price = $price;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set product name
     *
     * @param string $productName
     */
    public function setProductName(?string $productName)
    {
        $this->productName = $productName;
    }

    /**
     * @param null|string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @param int|null $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @param null|string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @param string|null $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }



}
