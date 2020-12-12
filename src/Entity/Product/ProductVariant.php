<?php

namespace App\Entity\Product;
//use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Price;
use App\Entity\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_variant")
 * @ ORM\Entity(repositoryClass="App\Entity\Product\Repository\ProductAttributeRepository")
 */
class ProductVariant //implements \JsonSerializable
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="id", type="integer", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({
     *     "productView", "productList",
     * })
     *
     * @ORM\Column(name="variant_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a variantnak.")
     */
    private $name;

//    /**
//     * @var ProductOption[]
//     * @Groups({"productView", "productList"})
//     *
//     * @ORM\ManyToMany(targetEntity="App\Entity\Product\ProductOption", inversedBy="variants")
//     * @ORM\JoinTable(name="product_option_selected_variants",
//     *      joinColumns={@ORM\JoinColumn(name="variant_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")}
//     *      )
//     * @ORM\OrderBy({"name": "ASC"})  // option_name
//     * @Assert\NotNull()
//     *          message="Hiányzik legalább egy termékopció!"
//     */
//    private $options;

    /**
     * @var ProductSelectedOption[]|ArrayCollection|null
     * @Groups({"productView", "productList"})
     * @MaxDepth(2)
     *
     * ==== One Variant belongs/is linked to many SelectedVariants ====
     *
     * @ORM\OneToMany(targetEntity="ProductSelectedOption", mappedBy="variant", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="id", referencedColumnName="variant_id", nullable=false)
     * @ ORM\OrderBy({"ordering": "ASC"})
     * @ Assert\NotBlank(message="Egy terméknek legalább egy kép szükséges.")
     */
    private $selectedOptions;

    /**
     * @var Product
     * @ Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\Product", inversedBy="variants", fetch="EAGER")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy terméket.")
     */
    private $product;

    /**
     * @var int|null
     * @Groups({"productView", "productList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="position", nullable=true, options={"default"="100"})
     */
    private $position = null;

    /**
     * @var Price|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Price", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", nullable=false)
     * @Assert\Type(type="App\Entity\Price")
     * @Assert\Valid
     * @ Assert\NotNull(message="Adj árat a terméknek.")
     */
    private $price;

    /**
     * @var ProductImage|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Product\ProductImage", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=false)
     * @ Assert\Type(type="App\Entity\Price")
     * @Assert\Valid
     */
    private $image;

    /**
     * @var string|null
     * @Groups({"productView", "productList",
     *     "orderView"})
     *
     * @ Assert\NotBlank(message="Nem adtál meg SKU-t.")
     * @ORM\Column(name="sku", type="string", length=100, nullable=true)
     */
    private $sku;

    public function __construct()
    {
        $this->selectedOptions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'sku'           => $this->getSku(),
            'position'      => $this->getPosition(),
            'price'         => $this->getPrice(),
            'image'         => $this->getImage(),
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     */
    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return Price|null
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    /**
     * @param Price|null $price
     */
    public function setPrice(?Price $price): void
    {
        $this->price = $price;
    }

    /**
     * @return ProductImage|null
     */
    public function getImage(): ?ProductImage
    {
        return $this->image;
    }

    /**
     * @param ProductImage|null $image
     */
    public function setImage(?ProductImage $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

//    /**
//     * @ return ProductOption[]|Collection
//     */
//    public function getOptions() //: Collection
//    {
//        return $this->options;
//    }
//
//    public function addOption(ProductOption $item): void
//    {
//        if (!$this->options->contains($item)) {
//            $this->options->add($item);
//        }
//    }
//
//    public function removeOption(ProductOption $item): void
//    {
//        $this->options->removeElement($item);
//    }
//
//    /**
//     * @return bool
//     */
//    public function hasOptions(): bool
//    {
//        return $this->options->isEmpty() ? false : true;
//    }

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
     * @return ProductSelectedOption[]|Collection
     */
    public function getSelectedOptions()
    {
        return $this->selectedOptions;
    }

    public function addSelectedOption(ProductSelectedOption $item): void
    {
        if (!$this->selectedOptions->contains($item)) {
            $item->setVariant($this);
            $this->selectedOptions->add($item);
        }
    }

    public function removeSelectedOption(ProductSelectedOption $item): void
    {
        $this->selectedOptions->removeElement($item);
    }

    /**
     * @return bool
     */
    public function hasSelectedOptions(): bool
    {
        return $this->selectedOptions->isEmpty() ? false : true;
    }


}