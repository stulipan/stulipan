<?php

namespace App\Entity\Product;

//use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\Criteria;
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
 * @ORM\Table(name="product_variant_selected_options")
 */
class ProductSelectedOption //implements \JsonSerializable
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
     * @var ProductVariant
     * @ Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductVariant", inversedBy="selectedOptions")
     * @ORM\JoinColumn(name="variant_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy termékvariációt.")
     */
    private $variant;

    /**
     * @var ProductOption
     * @Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductOption", inversedBy="selectedOptions")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy termékopciót.")
     */
    private $option;

    /**
     * @var ProductOptionValue
     * @Groups({"productView", "productList"})
     *
     * ==== Many SelectedVariants have the same OptionValue ====
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductOptionValue", cascade={"persist"})
     * @ORM\JoinColumn(name="option_value_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="A SelectedVariant -nak kell legyen egy OptionValue-je.")
     */
    private $optionValue;


    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function __toString(): string
    {
        return $this->optionValue->getValue();
    }

    /**
     * @return ProductVariant
     */
    public function getVariant(): ProductVariant
    {
        return $this->variant;
    }

    /**
     * @param ProductVariant $variant
     */
    public function setVariant(ProductVariant $variant): void
    {
        $this->variant = $variant;
    }

    /**
     * @return ProductOption
     */
    public function getOption(): ProductOption
    {
        return $this->option;
    }

    /**
     * @param ProductOption $option
     */
    public function setOption(ProductOption $option): void
    {
        $this->option = $option;
    }

    /**
     * @return ProductOptionValue|null
     */
    public function getOptionValue(): ?ProductOptionValue
    {
        return $this->optionValue;
    }

    /**
     * @param ProductOptionValue $value
     */
    public function setOptionValue(ProductOptionValue $value): void
    {
        $this->optionValue = $value;
    }

}