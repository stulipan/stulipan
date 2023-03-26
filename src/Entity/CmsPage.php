<?php

namespace App\Entity;

use App\Services\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CmsPageRepository")
 * @ORM\Table(name="cms_page")
 * @UniqueEntity("slug", message="Ilyen 'slug' már létezik!")
 */
class CmsPage
{
    use ImageEntityTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", name="id", nullable=false, options={"unsigned"=true})
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
     * @ORM\Column(name="page_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a CMS oldalnak.")
     */
    private $name;

    /**
     * @var string|null
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=false, unique=true)
     * @ Assert\NotBlank(message="A slug nem lehet üres. Pl: homepage")
     */
    private $slug;

    /**
     * @var string|null
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true)
     */
    private $content;


    /**
     * @var CmsPage|null
     *
     * ==== One parent is a CmsPage ====
     *
     * @ MaxDepth(1)
     * @ORM\ManyToOne(targetEntity="App\Entity\CmsPage", inversedBy="subpages", cascade={"persist"}) //
     * @ORM\JoinColumn(name="parent_page_id", referencedColumnName="id") //, nullable=true
     * Assert\NotBlank(message="Legalább egy apa CMS oldal kell legyen.")
     */
    private $parent;

    /**
     * @var CmsPage[]|ArrayCollection|null
     *
     * ==== One page may have subpages ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CmsPage", mappedBy="parent")
     * @ORM\JoinColumn(name="id", referencedColumnName="parent_page_id", nullable=true)
     * @ORM\OrderBy({"name" = "ASC"})
     * @Assert\NotBlank(message="Egy CMS oldalhoz több aloldal tartozhat.")
     */
    private $subpages;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=false})
     */
    private $enabled = 0;

    /**
     * @var CmsSection[]|ArrayCollection|null
     *
     * ==== One CMS Page has many product HTML blocks ====
     *
     * @ORM\OneToMany(targetEntity="CmsSection", mappedBy="page", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="block_id", nullable=false)
     * @ Assert\NotBlank(message="Egy HTML modulnak legalább egy CMS oldalhoz tartozik.")
     */
    private $htmlBlocks;

    public function __construct()
    {
        $this->subpages = new ArrayCollection();
        $this->htmlBlocks = new ArrayCollection();
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
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * @param string $name
     * @return CmsPage
     */
    public function setName($name)
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
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;
    }
    
    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(?string $content)
    {
        $this->content = $content;
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
     * @return CmsPage|null
     */
    public function getParent(): ?CmsPage
    {
        return $this->parent;
    }

    /**
     * @param CmsPage|null $parent
     */
    public function setParent(?CmsPage $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param CmsPage $item
     */
    public function addSubpage(CmsPage $item)
    {
        if (!$this->subpages->contains($item)) {
            $item->setParent($this);
            $this->subpages->add($item);
        }
    }

    /**
     * @param CmsPage $item
     */
    public function removeSubpage(CmsPage $item)
    {
        $item->setParent(null);
        $this->subpages->removeElement($item);
    }

    /**
     * @return CmsPage[]|Collection|null
     */
    public function getSubpages(): ?Collection
    {
        return $this->subpages->isEmpty() ? null : $this->subpages;
    }

    /**
     * @return CmsSection[]|Collection|null
     */
    public function getHtmlBlocks(): ?Collection
    {
        return $this->htmlBlocks->isEmpty() ? null : $this->htmlBlocks;
    }

    /**
     * @param CmsSection $item
     */
    public function addHtmlBlock(CmsSection $item)
    {
        if (!$this->htmlBlocks->contains($item)) {
            $item->setPage($this);
            $this->htmlBlocks->add($item);
        }
    }

    /**
     * @param CmsSection $item
     */
    public function removeHtmlBlock(CmsSection $item)
    {
        $item->setPage(null);
        $this->htmlBlocks->removeElement($item);
    }
}