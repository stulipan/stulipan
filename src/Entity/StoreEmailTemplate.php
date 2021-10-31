<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_email_template")
 * @UniqueEntity("slug", message="Ilyen 'slug' már létezik!")
 */
class StoreEmailTemplate
{
    public const ORDER_CONFIRMATION             = 'order-confirmation';
    public const SHIPPING_CONFIRMATION          = 'shipping-confirmation';
    public const ADMIN_NEW_ORDER_NOTIFICATION   = 'admin-new-order-notification';

    public const FORGOTTEN_PASSWORD             = 'forgotten-password';
//    public const SLUG_SHIPPING_INFORMATION     = 'shipping-information';
//    public const SLUG_RETURN_POLICY            = 'return-policy';
//    public const SLUG_CONTACT_INFORMATION      = 'contact-information';
//    public const SLUG_LEGAL_NOTICE             = 'legal-notice';


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
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     * @Assert\NotBlank(message="Adj nevet az email sablonnak.")
     */
    private $name;

    /**
     * @var string
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="slug", type="string", length=255, nullable=false, unique=true)
     * @ Assert\NotBlank(message="A slug nem lehet üres. Pl: order-confirmed")
     */
    private $slug;

    /**
     * @var string
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="A subject nem lehet üres.")
     */
    private $subject;

    /**
     * @var string
     * @Groups({
     *     "view", "list"
     * })
     *
     * @ORM\Column(name="body", type="text", length=65535, nullable=false)
     * @Assert\NotBlank(message="A body nem lehet üres.")
     */
    private $body;

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
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }
}