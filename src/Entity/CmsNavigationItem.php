<?php

namespace App\Entity;

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
 * @ORM\Table(name="cms_navigation_item")
 * @ UniqueEntity("slug", message="Ilyen slug már létezik!")
 */
class CmsNavigationItem implements JsonSerializable
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
     * @ORM\Column(name="item_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a HTML modulnak.")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false, unique=true)
     * @Assert\NotBlank(message="A slug nem lehet üres. Pl: homepage")
     */
    private $url;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=true})
     */
    private $enabled = true;

    /**
     * @var CmsNavigation|null
     *
     * ==== Many NavigationItems in one CMS Navigation ====
     *
     * @ORM\ManyToOne(targetEntity="CmsNavigation", inversedBy="navigationItems")
     * @ORM\JoinColumn(name="navigation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ Assert\NotBlank(message="Rendelj legalább egy HTML modult a CMS oldalhoz.")
     */
    private $navigation;

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'url'               => $this->getSlug(),
        ];
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
//    /**
//     * The setId is required for the Serializer/Normalizer to be able to create
//     * the subentities, and it must return the current entity !!
//     *
//     * @param int $id
//     * @return CmsPage
//     */
//    public function setId(int $id): CmsPage
//    {
//        $this->id = $id;
//        return $this;
//    }
    
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

    public function __toString(): ?string
    {
        return $this->getName();
    }
    
    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    /**
     * @param string|null $url
     */
    public function setUrl(?string $url)
    {
        $this->url = $url;
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
     * @return CmsNavigation|null
     */
    public function getNavigation(): ?CmsNavigation
    {
        return $this->navigation;
    }

    /**
     * @param CmsNavigation|null $navigation
     */
    public function setNavigation(?CmsNavigation $navigation): void
    {
        $this->navigation = $navigation;
    }
    

}