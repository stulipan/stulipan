<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CmsSectionRepository")
 * @ORM\Table(name="cms_section")
 * @UniqueEntity("slug", message="Ilyen slug már létezik!")
 */
class CmsSection
{
    public const HOMEPAGE = 'homepage';
    public const PRODUCT_PAGE = 'product';
    public const COLLECTION_PAGE = 'collection';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
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
     * @ORM\Column(name="block_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a HTML modulnak.")
     */
    private $name;

    /**
     * @var string
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=false, unique=true)
     * @Assert\NotBlank(message="A slug nem lehet üres. Pl: homepage")
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;
    
    /**
     * @var bool
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=false})
     */
    private $enabled = false;

    /**
     * @var bool
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="previewable", type="boolean", nullable=false, options={"default"=false})
     */
    private $previewable = false;

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
     * @var string|null
     *
     * @ORM\Column(name="belongs_to", type="text", length=65535, nullable=true)
     */
    private $belongsTo;

//    /**
//     * @var CmsPage|null
//     *
//     * ==== Many HTML blocks in one CMS Page ====
//     *
//     * @ORM\ManyToOne(targetEntity="CmsPage", inversedBy="htmlBlocks")
//     * @ORM\JoinColumn(name="block_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
//     * @ Assert\NotBlank(message="Rendelj legalább egy HTML modult a CMS oldalhoz.")
//     */
//    private $page;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): CmsSection
    {
        $this->id = $id;
        return $this;
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
     * @return CmsPage
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
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
    
    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;
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
     * @return bool
     */
    public function isPreviewable(): bool
    {
        return null === $this->previewable ? false : $this->previewable;
    }

    /**
     * @param bool $previewable
     */
    public function setPreviewable(bool $previewable): void
    {
        $this->previewable = $previewable;
    }



    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getBelongsTo(): ?string
    {
        return $this->belongsTo;
    }

    /**
     * @param string|null $belongsTo
     */
    public function setBelongsTo(?string $belongsTo): void
    {
        $this->belongsTo = $belongsTo;
    }



//    /**
//     * @return CmsPage|null
//     */
//    public function getPage(): ?CmsPage
//    {
//        return $this->page;
//    }
//
//    /**
//     * @param CmsPage|null $page
//     */
//    public function setPage(?CmsPage $page): void
//    {
//        $this->page = $page;
//    }
}