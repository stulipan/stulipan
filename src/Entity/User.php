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
 * @UniqueEntity("email", message="Az email címet elgépelted, vagy már regisztráltál vele!")
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
     * @Assert\NotBlank(message="Adj meg egy jelszót!")
     */
    private $password;

    /**
     * @var string
     * @Groups({"orderView", "orderList"})
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Írd be az email címedet!")
     * @Assert\Email(message="Ellenőrizd, hogy helyesen írtad be az email címet!")
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
     * @var Recipient[]|ArrayCollection|null
     *
     * ==== One User/Customer has Recipients ====
     * ==== mappedBy="customer" => az Recipients entitásban definiált 'customer' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Recipient", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="customer_id", nullable=true)
     * @Assert\NotBlank(message="Egy felhasználónak több címzetje lehet.")
     */
    private $recipients;

    /**
     * @var Sender[]|ArrayCollection|null
     *
     * ==== One User/Customer has Senders ====
     * ==== mappedBy="customer" => a Senders entitásban definiált 'customer' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Sender", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="customer_id", nullable=true)
     * @Assert\NotBlank(message="Egy felhasználónak több számlázási címe lehet.")
     */
    private $senders;

    /**
     * @var Order[]|ArrayCollection|null
     *
     * ==== One User/Customer has Orders ====
     * ==== mappedBy="customer" => az Order entitásban definiált 'customer' attribútumról van szó ====
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="customer", orphanRemoval=true)  //, cascade={"persist"}
     * @ORM\JoinColumn(name="id", referencedColumnName="customer_id", nullable=true)
     * @ORM\OrderBy({"id" = "DESC"})
     * @ Assert\NotBlank(message="Egy felhasználónak több rendelése lehet.")
     */
    private $orders = [];

    /**
     * @ ORM\Column()
     * @ORM\Column(name="role", type="simple_array", nullable=true)
     */
    private $roles = [];

    public function __construct()
    {
        $this->isActive = true;
        $this->recipients = new ArrayCollection();
        $this->senders = new ArrayCollection();
        $this->orders = new ArrayCollection();
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
        $this->firstname = $name;
    }

    /**
     * @param string $name
     */
    public function setLastname(?string $name)
    {
        $this->lastname = $name;
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

    /**
     * @return Order[]|Collection
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }
    
    /**
     * @return Order[]|Collection
     *
     */
    public function getOrdersPlaced(): Collection
    {
        $realOrders = new ArrayCollection();
        foreach ($this->orders as $order) {
            if ($order->getStatus() !== null) {
                $realOrders->add($order);
            }
        }

//        if ($realOrders->isEmpty()) {
//            return null;
//        }
        return $realOrders;
    }

    /**
     * @return Order[]|Collection
     */
    public function getLastOrder()
    {
        return $this->getOrdersPlaced()->first();
    }

    /**
     * @return bool
     */
    public function hasOrder(Order $order)
    {
        return $this->orders->contains($order);
    }

    /**
     * @return int
     */
    public function countOrders(): int
    {
        return $this->orders->count();
    }
    
    /**
     * @return int
     */
    public function countRealOrders(): int
    {
        $realOrders = new ArrayCollection();
        foreach ($this->orders as $order) {
            if ($order->getStatus() !== null) {
                $realOrders->add($order);
            }
        }
        return $realOrders->count();
    }

    /**
     * @return float
     */
    public function getSpentAmount(): float
    {
        $spent = 0;
        foreach ($this->getOrdersPlaced() as $o => $order) {
            $spent += $order->getSummary()->getTotalAmountToPay();
        }
        return (float) $spent;
    }
}