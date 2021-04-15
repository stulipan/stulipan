<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="myuser")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email", message="registration.email-is-in-use")
 */
class User implements UserInterface, Serializable
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="registration.password-is-missing")
     */
    private $password;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="registration.email-is-missing")
     * @Assert\Email(message="registration.email-is-invalid")
     */
    private $email;

    /**
     * @var int|null
     *
     * @ORM\Column(name="phone", type="string", length=15, nullable=false)
     * @ Assert\NotBlank(message="Add meg a telefonszámot.")
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="verified_email", type="smallint", length=1, nullable=false, options={"default"="0"})
     */
    private $verifiedEmail = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="accepts_marketing", type="smallint", length=1, nullable=false, options={"default"="0"})
     */
    private $acceptsMarketing = 0;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=1000, nullable=true)
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg" }, groups = {"create"})
     */
    private $image;

    /**
     * @var Customer|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== One User is a Customer ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Customer", mappedBy="user")
     */
    private $customer;

    /**
     * @ ORM\Column()
     * @ORM\Column(name="role", type="simple_array", nullable=true)
     */
    private $roles = [];

    public function __construct()
    {
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
    }

//    public function __toString(): string
//    {
//        return $this->getFullname();
//    }

    //    public function getRoles()
//    {
//        return array('ROLE_ADMIN');
//    }

    /**
     * @see UserInterface
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';  // guarantees every user at least has ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        return (string) $this->username;
    }

    /**
     * @param null|string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @see UserInterface
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using bcrypt or argon
        return null;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return bool
     */
    public function isVerifiedEmail(): bool
    {
        return 1 !== $this->verifiedEmail ? false : true;
    }

    /**
     * @param bool $verifiedEmail
     */
    public function setVerifiedEmail(bool $verifiedEmail): void
    {
        $this->verifiedEmail = $verifiedEmail;
    }

    /**
     * @return bool
     */
    public function isAcceptsMarketing(): bool
    {
        return 1 !== $this->acceptsMarketing ? false : true;
    }

    /**
     * @param bool $acceptsMarketing
     */
    public function setAcceptsMarketing(bool $acceptsMarketing): void
    {
        $this->acceptsMarketing = $acceptsMarketing;
    }

    /**
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $name
     */
    public function setFirstname(?string $name)
    {
        $this->firstname = $this->ucWords($name);
    }

    /**
     * @param string $name
     */
    public function setLastname(?string $name)
    {
        $this->lastname = $this->ucWords($name);
    }

    /**
     * @return string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @Groups({"orderView", "orderList"})
     * @return string
     */
    public function getFullname(): ?string
    {
        if (!$this->firstname && !$this->lastname) {
            return null;
        }
        $fullname = $this->firstname.' '.$this->lastname;
        return $fullname;
    }

    /**
     * @return null|string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param null|string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized, array('allowed_classes' => false));
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()

    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Customer|null
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     */
    public function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return bool
     */
    public function hasCustomer(): bool
    {
        return $this->customer ? true : false;
    }

    private function ucWords (?string $string)
    {
        return $string ? ucwords($string) : $string;
    }
}