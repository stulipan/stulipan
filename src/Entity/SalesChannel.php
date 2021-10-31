<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Product\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Table(name="sales_channel")
 * @ORM\Entity
 *
 * @UniqueEntity("shortcode", message="Ez a shortcode már használatban van!")
 */

class SalesChannel implements JsonSerializable
{
    public const STORE = 'store';
    public const FACEBOOK = 'facebook';
    public const GOOGLE ='google';

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
     * @Assert\NotBlank(message="A sales channel megnevezése hiányzik!")
     * @ORM\Column(name="channel_name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var string
     * @Groups({"productView", "productList"})
     *
     * @Assert\NotBlank(message="A rövid kód hiányzik!")
     * @ORM\Column(name="shortcode", type="string", length=255, nullable=false)
     */
    private $shortcode;

    /**
     * @var string
     *
     * @ORM\Column(name="short", type="string", length=255, nullable=false)
     * @ Assert\NotBlank(message="A sales channel rövid rövid leírása hiányzik!")
     */
    private $short;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     * @ Assert\NotBlank(message="A sales channel részletes leírása hiányzik!")
     */
    private $description;

    /**
     * @var int
     * @Groups({"productView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", nullable=true, options={"default"="100"})
     */
    private $ordering;

    /**
     * @var bool
     * @Groups({"productView"})
     *
     * @ORM\Column(name="enabled", type="smallint", nullable=false, options={"default"="1"})
     */
    private $enabled = '1';

    /**
     * @var Product[]|ArrayCollection | null
     *
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Product\Product", mappedBy="salesChannels")
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
            'shortcode'     => $this->getShortcode(),
            'short'         => $this->getShort(),
            'description'   => $this->getDescription(),
            'enabled'       => $this->isEnabled(),
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
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
    
    /**
     * @return string|null
     */
    public function getShortcode(): ?string
    {
        return $this->shortcode;
    }
    
    /**
     * @param string|null $shortcode
     */
    public function setShortcode(?string $shortcode)
    {
        $this->shortcode = $shortcode;
    }

    /**
     * @return string|null
     */
    public function getShort(): ?string
    {
        return $this->short;
    }

    /**
     * @param string|null $description
     */
    public function setShort(?string $description): void
    {
        $this->short = $description;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int|null
     */
    public function getOrdering(): ?int
    {
        return (int) $this->ordering;
    }

    /**
     * @param int|null $ordering
     */
    public function setOrdering(?int $ordering): void
    {
        $this->ordering = $ordering;
    }

    /**
     * @return bool
     */
    public function getEnabled(): ?bool
    {
        return 1 !== $this->enabled ? false : true;
//            $this->enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return 1 !== $this->enabled ? false : true;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Product[]|Collection|null
     */
    public function getProducts(): ?Collection
    {
        return $this->products->isEmpty() ? null : $this->products;
    }

//    /**
//     * @param Product $item
//     */
//    public function addProduct(Product $item)
//    {
//        if (!$this->products->contains($item)) {
//            $item->addBadge($this);
//            $this->products->add($item);
//        }
//    }
//
//    /**
//     * @param Product $item
//     */
//    public function removeProduct(Product $item)
//    {
//        $item->removeBadge($this);
//        $this->products->removeElement($item);
//    }
}