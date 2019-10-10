<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Imagine\Gd\Image;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity
 * @ORM\Table(name="image")
 */

class ImageEntity implements \JsonSerializable
{
    /**
     * @var int
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string|null
     *
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    private $alt;
    /**
     * @var string
     * @Groups({
     *     "main",
     *     "productView", "productList"
     * })
     *
     * @ORM\Column(name="file", type="string", nullable=false)
     *
     * @Assert\NotBlank(message="Tölts fel egy képet.")
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg", "image/jpg" }, mimeTypesMessage="blabla")
     */
    private $file;
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'            => $this->getId(),
            'file'          => $this->getFile(),
        ];
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * The setId is required for the Serializer/Normalizer to be able to create
     * the subentities, and it must return the current entity !!
     *
     * @param int $id
     * @ return ImageEntity
     */
    public function setId(int $id)//: ImageEntity
    {
        $this->id = $id;
//        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getAlt(): ?string
    {
        return $this->alt;
    }
    /**
     * @param string $alt
     */
    public function setAlt(?string $alt)
    {
        $this->alt = $alt;
    }
    
    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
    
    /**
     * @param string $file
     */
    public function setFile(?string $file)
    {
        $this->file = $file;
    }
}