<?php

namespace App\Entity\Product;

//use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\Criteria;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Entity\ImageEntity;
use App\Services\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 *  (repositoryClass="App\Repository\ProductCategoryRepository")
 * @ORM\Table(name="product_option")
 */
class ProductOption implements JsonSerializable
{
    /**
     * @var int
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(type="smallint", name="id", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Product
     * @ Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\Product", inversedBy="options", fetch="EAGER")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy terméket.")
     */
    private $product;

    /**
     * @var string
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="option_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a terméktulajdonságnak.")
     */
    private $name;

    /**
     * @var int
     * @Groups({"productView", "productList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="position", type="smallint", length=11, nullable=false, options={"default"="100"})
     */
    private $position;

    /**
     * @var ProductOptionValue[]|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductOptionValue", mappedBy="option", orphanRemoval=true, cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="id", referencedColumnName="product_id")
     * @ORM\OrderBy({"position" = "ASC"})
     * @Assert\NotBlank(message="Hiányoznak értekek a termékopcióból.")
     */
    private $values;

//    /**
//     * @var ProductVariant[]
//     * @ Groups({"productView", "productList"})
//     *
//     * @ORM\ManyToMany(targetEntity="App\Entity\Product\ProductVariant", mappedBy="options")
//     * @ ORM\JoinTable(name="product_option_selected_variants",
//     *      joinColumns={@ORM\JoinColumn(name="variant_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")}
//     *      )
//     * @ ORM\OrderBy({"name": "ASC"})  // option_name
//     * @ Assert\NotNull()
//     *          message="Hiányzik legalább egy termékopció!"
//     */
//    private $variants;

    /**
     * @var ProductSelectedOption[]|ArrayCollection|null
     * @ Groups({"productView", "productList"})
     *
     * ==== One Option belongs/is linked to many SelectedVariants ====
     *
     * @ORM\OneToMany(targetEntity="ProductSelectedOption", mappedBy="option", fetch="EXTRA_LAZY")     //orphanRemoval=true, cascade={"persist", "remove"}
     * @ORM\JoinColumn(name="id", referencedColumnName="option_id", nullable=false)
     * @ ORM\OrderBy({"ordering": "ASC"})
     * @ Assert\NotBlank(message="Egy terméknek legalább egy kép szükséges.")
     */
    private $selectedOptions;

    public function __construct()
    {
        $this->values = new ArrayCollection();
//        $this->variants = new ArrayCollection();
        $this->selectedOptions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'position'          => $this->getPosition(),
            'value'             => $this->getValues(),
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
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
     * @return int|null
     */
    public function getPosition(): ?int
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
     * @return ProductOptionValue[]|Collection|null
     */
    public function getValues()
    {
//        $criteria = Criteria::create()->orderBy(['position' => Criteria::ASC]);
//        return $this->values->matching($criteria);
//
        return $this->values;
    }

    public function addValue(ProductOptionValue $item): void
    {
        if (!$this->values->contains($item)) {
            $item->setOption($this);
            $this->values->add($item);
        }
    }

    public function removeValue(ProductOptionValue $item): void
    {
        $this->values->removeElement($item);
    }

    /**
     * @return bool
     */
    public function hasValues(): bool
    {
        return $this->values->isEmpty() ? false : true;
    }

    /**
     * @return ProductVariant[]|Collection
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * Finds a ProductOptionValue by its value
     * $criteria array must have a key called 'value'. Eg: ['value' => 'Green']
     *
     * @param array $criteria
     * @return ProductOptionValue|null
     */
    public function findValueBy($criteria = []): ?ProductOptionValue
    {
        if (isset($criteria['value'])) {
            return $value = $this->values->filter(
                function ($item) use ($criteria) {
                    return $item->getValue() === $criteria['value'] ? $item : null;
                }
            )->first();
        }
        return null;
    }

    /**
     * @return ProductSelectedOption[]|Collection|null
     */
    public function getSelectedOptions()
    {
        return $this->selectedOptions;
    }

    public function findSelectedOption(ProductOption $option, ProductOptionValue $optionValue)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('option', $option))
            ->andWhere(Criteria::expr()->eq('optionValue', $optionValue))
//            ->orderBy(['position' => Criteria::ASC])
        ;
        return $this->selectedOptions->matching($criteria)->first();
    }


}