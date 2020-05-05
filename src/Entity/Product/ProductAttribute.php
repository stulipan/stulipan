<?php

namespace App\Entity\Product;
//use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * (
 *     collectionOperations={"get", "post"},
 *     itemOperations={"get", "put", "delete"},
 *     normalizationContext={"groups"={"productView"}}
 * )
 *
 * @ORM\Entity
 * @ORM\Table(name="product_attribute")
 * @ ORM\Entity(repositoryClass="App\Entity\Product\Repository\ProductAttributeRepository")
 */
class ProductAttribute //implements \JsonSerializable
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"productView"})
     *
     * @ORM\Column(name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({"productView"})
     *
     * @ORM\Column(name="attribute_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Nevezd el a tulajdonságot!")
     */
    private $name;

    /**
     * @var ProductKind
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductKind", inversedBy="attributes")
     * @ORM\JoinColumn(name="kind_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Add meg a terméktípust amihez tartozik ez a tulajdonságérték!")
     */
    private $kind;

    /**
     * @var int
     * @Groups({"productView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", nullable=false, options={"default"="100"})
     */
    private $ordering;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'ordering'      => $this->getOrdering(),
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
     * @return string|null
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set product type name
     *
     * @param string $nev
     */
    public function setName(string $nev): void
    {
        $this->name = $nev;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return ProductKind
     */
    public function getKind(): ?ProductKind
    {
        return $this->kind;
    }

    /**
     * @param ProductKind $type
     *
     */
    public function setKind(?ProductKind $type): void
    {
        $this->kind = $type;
    }

    /**
     * @return int
     */
    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    /**
     * @param int $ordering
     */
    public function setOrdering(?int $ordering): void
    {
        $this->ordering = $ordering;
    }


}