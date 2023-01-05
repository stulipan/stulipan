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
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
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

    /**
     * @var Recipient[]|ArrayCollection|null
     *
     * ==== One User/Customer has Recipients ====
     * ==== mappedBy="customer" => az Recipients entitásban definiált 'customer' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Recipient", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=true)
     * @Assert\NotBlank(message="Egy felhasználónak több címzetje lehet.")
     */
    private $recipients;

    /**
     * @var Sender[]|ArrayCollection|null
     *
     * ==== One User/Customer has Senders ====
     * ==== mappedBy="customer" => a Senders entitásban definiált 'customer' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Sender", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=true)
     * @Assert\NotBlank(message="Egy felhasználónak több számlázási címe lehet.")
     */
    private $senders;

    public function __construct()
    {
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
        $this->recipients = new ArrayCollection();
        $this->senders = new ArrayCollection();
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

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

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
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // We're using bcrypt in security.yaml to encode the password, so
        // the salt value is built-in and and you don't have to generate one
        // See https://en.wikipedia.org/wiki/Bcrypt
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
        return null === $this->acceptsMarketing ? false : $this->acceptsMarketing;
    }

    /**
     * @param bool $acceptsMarketing
     */
    public function setAcceptsMarketing(bool $acceptsMarketing): void
    {
        $this->acceptsMarketing = $acceptsMarketing;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $name
     */
    public function setFirstname(?string $name)
    {
        $this->firstname = $this->ucWords($name);
    }

    /**
     * @param string|null $name
     */
    public function setLastname(?string $name)
    {
        $this->lastname = $this->ucWords($name);
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @Groups({"orderView", "orderList"})
     * @return string|null
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
    public function getInitials(): ?string
    {
        $fullnameInitial = $this->firstname[0].$this->lastname[0];
        return $this->ucWords($fullnameInitial);
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

    public function getAvatar()
    {
        if ($this->getImage() == null) {
            return $this->getInitials();
        }
        return $this->getImage();
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([
            $this->id,
            $this->username,
            $this->email,
            $this->password
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [
            $this->id,
            $this->username,
            $this->email,
            $this->password
        ] = unserialize($serialized, ['allowed_classes' => false]);
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

    /**
     * @param Recipient $recipient
     */
    public function addRecipient(Recipient $recipient): void
    {
        $this->recipients->add($recipient);
    }

    /**
     * @param Recipient $recipient
     */
    public function removeRecipient(Recipient $recipient): void
    {
        $this->recipients->removeElement($recipient);
    }

    /**
     * @return Recipient[]|Collection
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    /**
     * Checking if the Customer has Recipients.
     *
     * @return bool
     */
    public function hasRecipients(): bool
    {
        if ($this->recipients and !$this->recipients->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Sender $sender
     */
    public function addSender(Sender $sender): void
    {
        $this->senders->add($sender);
    }

    /**
     * @param Sender $sender
     */
    public function removeSender(Sender $sender): void
    {
        $this->senders->removeElement($sender);
    }

    /**
     * @return Sender[]|Collection
     */
    public function getSenders(): Collection
    {
        return $this->senders;
    }

    /**
     * Checking if the Customer has Senders.
     *
     * @return bool
     */
    public function hasSenders(): bool
    {
        if ($this->senders and !$this->senders->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }
}