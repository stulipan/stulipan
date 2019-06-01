<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Price
 *
 * ORM\Entity()
 */
class PriceX //extends Price
{
    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")  //, inversedBy="prices"
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Nem választottál terméket.")
     */
    private $product;

    public static function create(Product $product, float $grossPrice, bool $activated,
                                  VatRate $vatRate = null, \DateTime $expiredAt = null): Price
    {
        $self = new self();
        $self->product = $product;
        $self->grossPrice = $grossPrice;
//        $self->type = $type;
        $self->activated = $activated;
        $self->vatRate = $vatRate;
        $self->expiredAt = $expiredAt;

        return $self;
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
}
