<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=BlogRepository::class)
 * @UniqueEntity("slug", message="Ilyen 'slug' már létezik!")
 */
class Blog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $seoTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $seoDescription;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $isCommentsAllowed = false;

    /**
     * @var BlogArticle[]|ArrayCollection|null
     *
     * ==== One Blog has many product articles ====
     *
     * @ORM\OneToMany(targetEntity="BlogArticle", mappedBy="blog", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="blog_id", nullable=false)
     * @ORM\OrderBy({"publishedAt" = "DESC"})
     */
    private $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(string $seoTitle): self
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): self
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsCommentsAllowed(): ?bool
    {
        return null === $this->isCommentsAllowed ? false : $this->isCommentsAllowed;
    }

    public function setIsCommentsAllowed(bool $isCommentsAllowed): self
    {
        $this->isCommentsAllowed = $isCommentsAllowed;

        return $this;
    }

    /**
     * @return BlogArticle[]|ArrayCollection|null
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param BlogArticle $article
     */
    public function addArticle(BlogArticle $article)
    {
        if (!$this->articles->contains($article)) {
            $article->setBlog($this);
            $this->articles->add($article);
        }
    }

    /**
     * @param BlogArticle $article
     */
    public function removeArticle(BlogArticle $article)
    {
        $article->setBlog(null);
        $this->articles->removeElement($article);
    }

    /**
     * @return BlogArticle[]|ArrayCollection|null
     */
    public function getArticlesPublished()
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('enabled', true));

        if ($this->articles->matching($criteria)->isEmpty()) {
            return null;
        }
        return $this->articles->matching($criteria);
    }
}
