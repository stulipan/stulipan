<?php

namespace App\Entity\Product;

//use ApiPlatform\Core\Annotation\ApiResource;
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
 *
 * @ORM\Table(name="product_option_value")
 */
class ProductOptionValue implements JsonSerializable
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
     * @var ProductOption
     * @ Groups({"productView", "productList"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductOption", inversedBy="values", fetch="EAGER")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Válassz egy termékopciót.")
     */
    private $option;

    /**
     * @var string
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Hiányzik a termékopció értéke.")
     */
    private $value;

    /**
     * @var int
     * @Groups({"productView", "productList"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="position", type="smallint", length=11, nullable=false, options={"default"="100"})
     */
    private $position;

//    /**
//     * @var ProductSelectedVariant
//     * @ Groups({"productView", "productList"})
//     *
//     * ==== One OptionValue belongs to a SelectedVariant ====
//     *
//     * @ORM\OneToOne(targetEntity="App\Entity\Product\ProductSelectedVariant")
//     * @ ORM\JoinColumn(name="id", referencedColumnName="option_value_id", nullable=false)
//     * @ Assert\NotBlank(message="A SelectedVariant -nak kell legyen egy OptionValue-je.")
//     */
//    private $selectedVariant;

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'value'             => $this->getValue(),
            'position'          => $this->getPosition(),
        ];
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
        return $this->getValue();
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
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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
     * Returns the greatest position that values of a ProductOption can have.
     * The bellow logic assumes the OptionValue are sorted by position (ASC).
     *
     * @return int
     */
    public function getGreatestPosition()
    {
        return $this->option->getValues()->last() ? $this->option->getValues()->last()->getPosition() : 0;
    }

//    /**
//     * @return ProductSelectedVariant
//     */
//    public function getSelectedVariant(): ProductSelectedVariant
//    {
//        return $this->selectedVariant;
//    }
//
//    /**
//     * @param ProductSelectedVariant $selectedVariant
//     */
//    public function setSelectedVariant(ProductSelectedVariant $selectedVariant): void
//    {
//        $this->selectedVariant = $selectedVariant;
//    }

}