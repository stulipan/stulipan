<?php

namespace App\Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_attribute")
 * @ ORM\Entity(repositoryClass="App\Repository\ProductAttributeRepository")
 */
class ProductAttribute
{
    use TimestampableTrait;

    /**
     *
     * @ORM\Column(name="id", length=5, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     *
     * @ORM\Column(name="attribute_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Nevezd el a tulajdonságértéket!")
     */
    private $name='';

    /**
     * @var ProductKind
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProductKind", inversedBy="attributes")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Add meg a terméktípust amihez tartozik ez a tulajdonságérték!")
     */
    private $kind;

    /**
     * Get id
     *
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

    public function __toString()
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


}