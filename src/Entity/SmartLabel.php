<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmartLabelRepository")
 * @ORM\Table(name="smart_label")
 */
class SmartLabel
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
     *
     * @ORM\Column(name="label_name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var boolean|null
     *
     * @ORM\Column(name="is_enabled", type="boolean", nullable=false, options={"default"=false})
     */
    private $enabled;
    
    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ordering", type="smallint", nullable=false, options={"default"=100, "unsigned"=true})
     */
    private $ordering;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $backgroundColor;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $textColor;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $textStyle;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $textSize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mobileSizeSameAsDesktop;

    /**
     * @var ImageEntity|null
     * @ORM\OneToOne(targetEntity="App\Entity\ImageEntity", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     */
    private $image;

    /**
     * @return int
     */
    public function getId(): int
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
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return null === $this->enabled ? false : $this->enabled;
    }

    /**
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
    }

    /**
     * @param int $ordering
     */
    public function setOrdering(int $ordering): void
    {
        $this->ordering = $ordering;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    public function setTextColor(?string $textColor): self
    {
        $this->textColor = $textColor;

        return $this;
    }

    public function getTextStyle(): ?int
    {
        return $this->textStyle;
    }

    public function setTextStyle(?int $textStyle): self
    {
        $this->textStyle = $textStyle;

        return $this;
    }

    public function getTextSize(): ?int
    {
        return $this->textSize;
    }

    public function setTextSize(int $textSize): self
    {
        $this->textSize = $textSize;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getMobileSizeSameAsDesktop(): ?bool
    {
        return $this->mobileSizeSameAsDesktop;
    }

    public function setMobileSizeSameAsDesktop(?bool $mobileSizeSameAsDesktop): self
    {
        $this->mobileSizeSameAsDesktop = $mobileSizeSameAsDesktop;

        return $this;
    }

    public function getImage(): ?ImageEntity
    {
        return $this->image;
    }

    public function setImage(?ImageEntity $image): self
    {
        $this->image = $image;

        return $this;
    }
    
}