<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Model\DeliveryDate;
use App\Entity\Product\Product;

/**
 *
 */
class AddToCartModel
{
    /**
     * @var int
     */
    private $productId;

    private $product;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var DeliveryDate|null
     */
    private $deliveryDate;

    public function __construct(int $productId = null, int $quantity = null, ?DeliveryDate $deliveryDate = null)
//    public function __construct(Product $product, int $quantity, ?DeliveryDate $deliveryDate)
    {
        $this->productId = $productId;
//        $this->product = $product;
        $this->quantity = $quantity;
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }


    /**
     * @return DeliveryDate|null
     */
    public function getDeliveryDate(): ?DeliveryDate
    {
        return $this->deliveryDate;
    }

    /**
     * @param DeliveryDate|null $deliveryDate
     */
    public function setDeliveryDate(?DeliveryDate $deliveryDate): void
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
