<?php

namespace App\Entity\Product;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 *
 * @ORM\Entity
 * @ORM\Table(name="product_status")
 */
class ProductStatus implements \JsonSerializable
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
     * @ORM\Column(name="status_name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var string|null
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="icon", type="string", length=200, nullable=true)
     */
    private $icon;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'icon'          => $this->getIcon(),
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
    public function getIcon(): ?string
    {
        return $this->icon;
    }
    
    /**
     * @param string $icon
     */
    public function setIcon(?string $icon)
    {
        $this->icon = $icon;
    }
    

}