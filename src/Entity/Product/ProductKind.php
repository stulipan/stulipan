<?php

namespace App\Entity\Product;
//use ApiPlatform\Core\Annotation\ApiResource;

use App\Entity\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * (
 *     normalizationContext={"groups"={"productView"}}
 * )
 *
 * @ORM\Entity
 * @ORM\Table(name="product_kind")
 */
class ProductKind implements JsonSerializable
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="kind_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a terméktípusnak!")
     */
    private $name;
    
    /**
     * @var bool
     * @Groups({"productView"})
     *
     * @ORM\Column(name="price_enabled", type="boolean", nullable=false, options={"default":0})
     */
    private $priceEnabled;

    /**
     * @var Product[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="kind", cascade={"persist"})
     */
    private $products;

    /**
     * ()
     *
     * @var ProductAttribute[]|ArrayCollection
     * @Groups({"productView"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductAttribute", mappedBy="kind", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"ordering" = "ASC"})
     * @ORM\JoinColumn(name="id", referencedColumnName="kind_id")
     */
    private $attributes;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
//            'products'      => $this->getProducts(),
//            'attributes'    => $this->getAttributes(),
        ];
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function __toString()
    {
        return $this->getName();
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    /**
     * @return bool
     */
    public function isPriceEnabled(): bool
    {
        return $this->priceEnabled;
    }
    /**
     * @return bool
     */
    public function getPriceEnabled(): bool
    {
        return $this->priceEnabled;
    }
    
    /**
     * @param bool $priceEnabled
     */
    public function setPriceEnabled(bool $priceEnabled)
    {
        $this->priceEnabled = $priceEnabled;
    }

    /**
     * @return Product[]|Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }
    
    public function addProduct(Product $product): void
    {
        if (!$this->products->contains($product)) {
            $product->setKind($this);
            $this->products->add($product);
        }
    }
//    public function addProduct(Product ...$products): void
//    {
//        foreach ($products as $product) {
//            if (!$this->products->contains($product)) {
//                $product->setKind($this);
//                $this->products->add($product);
//            }
//        }
//    }
    public function removeProduct(Product $product): void
    {
        $product->setKind(null);
        $this->products->removeElement($product);
    }

    /**
     * @return ProductAttribute[]|Collection
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }


}