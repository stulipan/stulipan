<?php

namespace App\Entity\Product;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 *
 * @ORM\Entity
 * @ORM\Table(name="product_badge")
 */
class ProductBadge implements \JsonSerializable
{

    /**
     * @var int
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="badge_name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="icon", type="string", length=200, nullable=true)
     */
    private $css;
    
    /**
     * @var int
     * @Groups({"productView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", nullable=false, options={"default"="100"})
     */
    private $ordering;
    
    /**
     * @var Product[] | ArrayCollection | null
     *
     *
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="badges")
     */
    private $products;
    
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'icon'          => $this->getCss(),
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
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->getName();
    }
    
    /**
     * @return null|string
     */
    public function getCss(): ?string
    {
        return $this->css;
    }
    
    /**
     * @param string $css
     */
    public function setCss(?string $css)
    {
        $this->css = $css;
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
    
    /**
     * @return Product[]|Collection|null
     */
    public function getProducts(): ?Collection
    {
        return $this->products->isEmpty() ? null : $this->products;
    }
    
    /**
     * @param Product $item
     */
    public function addProduct(Product $item)
    {
        if (!$this->products->contains($item)) {
            $item->addBadge($this);
            $this->products->add($item);
        }
    }
    
    /**
     * @param Product $item
     */
    public function removeProduct(Product $item)
    {
        $item->removeBadge($this);
        $this->products->removeElement($item);
    }

}