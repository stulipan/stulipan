<?php

namespace App\Entity\Product;
//use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 *
 * @ORM\Entity
 * @ORM\Table(name="product_status")
 */
class ProductStatus implements JsonSerializable
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_REMOVED = 'removed';

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
     * @var string
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(name="shortcode", type="string", length=20, nullable=false)
     */
    private $shortcode;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(name="css", type="string", length=20, nullable=true)
     */
    private $css;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'icon'          => $this->getIcon(),
            'shortcode'     => $this->getShortcode(),
            'css'           => $this->getCss(),
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

    /**
     * @return string
     */
    public function getShortcode(): ?string
    {
        return $this->shortcode;
    }

    /**
     * @param string $shortcode
     */
    public function setShortcode(?string $shortcode): void
    {
        $this->shortcode = $shortcode;
    }

    /**
     * @return string
     */
    public function getCss(): ?string
    {
        return $this->css;
    }

    /**
     * @param string $css
     */
    public function setCss(string $css): void
    {
        $this->css = $css;
    }


}