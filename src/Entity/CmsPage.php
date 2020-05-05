<?php

namespace App\Entity;

//use ApiPlatform\Core\Annotation\ApiResource;
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
 * @ ApiResource(
 *
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CmsPageRepository")
 * @ORM\Table(name="cms_page")
 * @UniqueEntity("slug", message="Ilyen 'handle' már létezik!")
 */
class CmsPage implements JsonSerializable
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
     * @ORM\Column(name="page_name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message="Adj nevet a CMS oldalnak.")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=100, nullable=false, unique=true)
     * @Assert\NotBlank(message="A slug nem lehet üres. Pl: homepage")
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true)
     */
    private $content;
    
    /**
     * @var ImageEntity|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ImageEntity", cascade={"persist"}) // No need for cascade={"persist"} as the ImageEntity will previously be saved to db
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     **/
    private $image;
    
    /**
     * @var string|null
     */
    private $imageUrl;

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
     * @var CmsHtmlBlock[]|ArrayCollection|null
     *
     * ==== One CMS Page has many product HTML blocks ====
     *
     * @ORM\OneToMany(targetEntity="CmsHtmlBlock", mappedBy="page", orphanRemoval=true, cascade={"persist", "remove"})
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
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'slug'              => $this->getSlug(),
            'content'           => $this->getContent(),
            'parent'            => $this->getParent(),
            'enabled'           => $this->getEnabled(),
            'image'             => $this->getImage(),
            'imageUrl'          => $this->getImageUrl(),
//            'subpages'     => $this->getSubpages(),
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
    public function setId(int $id): CmsPage
    {
        $this->id = $id;
        return $this;
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
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
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
     * @return ImageEntity
     */
    public function getImage(): ?ImageEntity
    {
        return $this->image;
    }
    
    /**
     * @param ImageEntity $image
     * @return CmsPage|null
     */
    public function setImage(?ImageEntity $image): ?CmsPage
    {
        $this->image = $image;
        return $this;
    }
    
    /**
     * This is used in ImageSetFullPath service. The service calls setImageUrl to set full URL to the image (eg: https://www....../image_filename.jpeg )
     * @param null|string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }
    
    /**
     * Return full URL: http://stulipan.dfr/media/cache/resolve/product_thumbnail/uploads/images/products/ethan-haddox-484912-unsplash-5ceea70235e84.jpeg
     * This is to be used API
     *
     * @return null|string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
    
    /**
     * Returns "products/image_filename.jpeg"
     * This is to be used in Twig templates with uploaded_asset()
     *
     * @return string
     */
    public function getImagePath(): ?string
    {
        if ($this->getImage()) {
            return FileUploader::PRODUCT_FOLDER.'/'.$this->getImage()->getFile();
        }
        return null;
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
     * @return CmsPage
     */
    public function getParent(): ?CmsPage
    {
        return $this->parent;
    }

    /**
     * @param CmsPage $parent
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
     * @return CmsHtmlBlock[]|Collection|null
     */
    public function getHtmlBlocks(): ?Collection
    {
        return $this->htmlBlocks->isEmpty() ? null : $this->htmlBlocks;
    }

    /**
     * @param CmsHtmlBlock $item
     */
    public function addHtmlBlock(CmsHtmlBlock $item)
    {
        if (!$this->htmlBlocks->contains($item)) {
            $item->setPage($this);
            $this->htmlBlocks->add($item);
        }
    }

    /**
     * @param CmsHtmlBlock $item
     */
    public function removeHtmlBlock(CmsHtmlBlock $item)
    {
        $item->setPage(null);
        $this->htmlBlocks->removeElement($item);
    }
}