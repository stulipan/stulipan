<?php

namespace App\Entity;

use JsonSerializable;
use phpDocumentor\Reflection\Types\This;
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
 * @ORM\Entity(repositoryClass="App\Repository\CmsNavigationRepository")
 * @ORM\Table(name="cms_navigation")
 * @ UniqueEntity("slug", message="Ilyen 'handle' már létezik!")
 */
class CmsNavigation implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint", name="id", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="navigation_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a CMS oldalnak.")
     */
    private $name;

//    /**
//     * @var string
//     *
//     * @ORM\Column(name="slug", type="string", length=100, nullable=false, unique=true)
//     * @Assert\NotBlank(message="A slug nem lehet üres. Pl: homepage")
//     */
//    private $slug;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=true})
     */
    private $enabled = true;

    /**
     * @var CmsNavigationItem[]|ArrayCollection|null
     *
     * ==== One CMS Page has many product HTML blocks ====
     *
     * @ORM\OneToMany(targetEntity="CmsNavigationItem", mappedBy="navigation", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="item_id", nullable=false)
     * @ Assert\NotBlank(message="Egy HTML modulnak legalább egy CMS oldalhoz tartozik.")
     */
    private $navigationItems;

    public function __construct()
    {
        $this->navigationItems = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'enabled'           => $this->getEnabled(),
            'navigationItems'   => $this->getNavigationItems(),
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
     * The setId is required for the Serializer/Normalizer to be able to create
     * the subentities, and it must return the current entity !!
     *
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
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
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function __toString(): ?string
    {
        return $this->getName();
    }
    
    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return null === $this->enabled ? false : $this->enabled;
    }

    /**
     * Returns true or false, after transformation (1 or 0 which are stored in db)
     * @return bool
     */
    public function isEnabled(): bool
    {
        return null === $this->enabled ? false : $this->enabled;
    }

    /**
     * Sets value to 1 or 0 which are stored in db
     * @param bool $enabled
     */
    public function setEnabled(?bool $enabled)
    {
//        $this->enabled = true === $enabled ? 1 : 0;
        $this->enabled = $enabled;
    }

    /**
     * @return CmsNavigationItem[]|ArrayCollection|null
     */
    public function getNavigationItems()
    {
        return $this->navigationItems;
    }

    /**
     * @param CmsNavigationItem $item
     */
    public function addNavigationItem(CmsNavigationItem $item)
    {
        if (!$this->navigationItems->contains($item)) {
            $item->setNavigation($this);
            $this->navigationItems->add($item);
        }
    }

    /**
     * @param CmsHtmlBlock $item
     */
    public function removeNavigationItem(CmsNavigationItem $item)
    {
        $item->setPage(null);
        $this->navigationItems->removeElement($item);
    }
}