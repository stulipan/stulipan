<?php

namespace App\Entity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @ UniqueEntity("email", message="Az email címet elgépelted, vagy már regisztráltál vele!")
 */
class Customer
{
    const OPTIN_LEVEL_SINGLE_OPTIN = "single_opt_in";
    const OPTIN_LEVEL_CONFIRMED_OPTIN = "confirmed_opt_in";
    const OPTIN_LEVEL_UNKNOWN = "unknown";

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
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="verified_email", type="smallint", length=1, nullable=false, options={"default"="0"})
     */
    private $verifiedEmail = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(name="accepts_marketing", type="smallint", length=1, nullable=true)
     */
    private $acceptsMarketing = 0;


    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="accepts_marketing_updated_at", type="datetime", nullable=true)
     *
     */
    protected $acceptsMarketingUpdatedAt;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="marketing_opt_in_level", type="string", length=30, nullable=true)
     */
    private $marketingOptinLevel;


    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="!")
     */
    private $firstname;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="!")
     */
    private $lastname;

    /**
     * @var User|null
     * @Groups({"orderView", "orderList"})
     *
     * ==== One Customer is linked to one User ====
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="customer")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

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
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="customer", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="customer_id", nullable=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @ Assert\NotBlank(message="Egy felhasználónak több rendelése lehet.")
     */
    private $orders = [];

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        if ($this->user && $this->user->getEmail()) {
            return $this->user->getEmail();
        }
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
     * @return string
     */
    public function getFirstname(): ?string
    {
//        if ($this->user && $this->user->getFirstname()) {
//            return $this->user->getFirstname();
//        }
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
//        if ($this->user && $this->user->getLastname()) {
//            return $this->user->getLastname();
//        }
        return $this->lastname;
    }

    /**
     * @Groups({"orderView", "orderList"})
     * @return string
     */
    public function getFullname(): ?string
    {
        if (!$this->getFirstname() && !$this->getLastname()) {
            return null;
        }
        $fullname = $this->getFirstname().' '.$this->getLastname();
        return $fullname;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @return DateTime|null
     */
    public function getAcceptsMarketingUpdatedAt(): ?DateTime
    {
        return $this->acceptsMarketingUpdatedAt;
    }

    /**
     * @param DateTime|null $acceptsMarketingUpdatedAt
     */
    public function setAcceptsMarketingUpdatedAt(?DateTime $acceptsMarketingUpdatedAt): void
    {
        $this->acceptsMarketingUpdatedAt = $acceptsMarketingUpdatedAt;
    }

    /**
     * @return string|null
     */
    public function getMarketingOptinLevel(): ?string
    {
        return $this->marketingOptinLevel;
    }

    /**
     * @param string|null $marketingOptinLevel
     */
    public function setMarketingOptinLevel(?string $marketingOptinLevel): void
    {
        $this->marketingOptinLevel = $marketingOptinLevel;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
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
     * @param Order|null $order
     */
    public function addOrder(?Order $order): void
    {
        $this->orders->add($order);
    }

    /**
     * @param Order $order
     */
    public function removeOrder(Order $order): void
    {
        $this->orders->removeElement($order);
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
     *
     */
    public function getPartialOrders(): Collection
    {
        $partialOrders = new ArrayCollection();
        return $this->orders->filter(function(Order $order) {
            return $order->getStatus() === null;
        });
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
     * @return bool
     */
    public function hasOrderPlaced(Order $order)
    {
        return $this->getOrdersPlaced()->contains($order);
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
    public function getPlacedOrdersCount(): int
    {
        $placedOrders = $this->getOrdersPlaced();
        if ($placedOrders) {
            return $placedOrders->count();
        }
        return 0;
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

    private function ucWords (?string $string)
    {
        return $string ? ucwords($string) : $string;
    }
}