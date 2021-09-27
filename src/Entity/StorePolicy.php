<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ ORM\Entity(repositoryClass="App\Repository\CmsPageRepository")
 * @ORM\Entity
 * @ORM\Table(name="store_policy")
 * @UniqueEntity("slug", message="Ilyen 'handle' már létezik!")
 */
class StorePolicy
{
    public const SLUG_TERMS_AND_CONDITIONS     = 'terms-and-conditions';
    public const SLUG_PRIVACY_POLICY           = 'privacy-policy';
    public const SLUG_SHIPPING_INFORMATION     = 'shipping-information';
    public const SLUG_RETURN_POLICY            = 'return-policy';
    public const SLUG_CONTACT_INFORMATION      = 'contact-information';
    public const SLUG_LEGAL_NOTICE             = 'legal-notice';


    /**
     * @var int
     *
     * @ORM\Column(type="smallint", name="id", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     * @ Assert\NotBlank(message="Adj nevet a Policy-nek.")
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
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true)
     */
    private $content;

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
    public function setId(int $id): StorePolicy
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
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

//    public function __toString(): ?string
//    {
//        return $this->getName();
//    }
    
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
}