<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_navigation_item")
 * @ UniqueEntity("slug", message="Ilyen slug már létezik!")
 */
class CmsNavigationItem
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="item_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a HTML modulnak.")
     */
    private $name;

    /**
     * @var string
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     * @ Assert\NotBlank(message="A slug nem lehet üres. Pl: homepage")
     */
    private $url;

    /**
     * @var bool
     * @Groups({
     *     "view", "list"
     * })
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
     * @var int
     * @Groups({
     *     "view", "list"
     * })
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", type="smallint", nullable=false, options={"default"=100, "unsigned"=true})
     */
    private $ordering;

    /**
     * @var string|null
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="classname", type="string", length=255, nullable=true)
     */
    private $classname;

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

    public function __toString(): string
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

    /**
     * @return int|null
     */
    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    /**
     * @param int|null $ordering
     */
    public function setOrdering(?int $ordering): void
    {
        $this->ordering = $ordering;
    }

    /**
     * @return string|null
     */
    public function getClassname(): ?string
    {
        return $this->classname;
    }

    /**
     * @param string|null $classname
     */
    public function setClassname(?string $classname): void
    {
        $this->classname = $classname;
    }
}
