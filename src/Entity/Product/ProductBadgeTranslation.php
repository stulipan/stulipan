<?php

namespace App\Entity\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Stulipan\Traducible\Entity\TraducibleInterface;
use Stulipan\Traducible\Entity\TranslationInterface;
use Stulipan\Traducible\Model\TraducibleTrait;
use Stulipan\Traducible\Model\TranslationTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 */
class ProductBadgeTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @var int
     * @Groups({"productView", "productList"})
     *
     * @ORM\Column(type="integer")
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
     * @return int|null
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
     * @param string|null $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->getName();
    }


}