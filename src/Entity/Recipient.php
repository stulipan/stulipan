<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\TimestampableTrait;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AssertApp;

/**
 *
 * @ORM\Table(name="recipient")
 * @ORM\Entity
 */
class Recipient
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=false)
     * @Assert\NotNull(message="checkout.recipient.missing-firstname")
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=false)
     * @Assert\NotNull(message="checkout.recipient.missing-lastname")
     */
    private $lastname;

    /**
     * @var Address
     *
     * ==== One Recipient has one Address ====
     *
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy címzetnek kell legyen egy címe.")
     * @Assert\Valid()
     */
    private $address;

    /**
     * @var User|null
     *
     * ==== Many Recipients belong to one Customer ====
     * ==== inversed By="recipients" => a User entitásban definiált 'recipients' attibútumról van szó; A Címzettet így kötjük vissza a Customerhez
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="recipients")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $user;


    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="checkout.recipient.missing-phone")
     * @ AssertApp\PhoneNumber()
     */
    private $phone;



    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getFullname();
    }

    /**
     * @return string|null
     */
    public function getFullname(): ?string
    {
        if ($this->lastname && $this->firstname) {
            return $this->lastname.' '.$this->firstname;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return Address
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @var Address $address
     */
    public function setAddress(Address $address): void
    {
        $this->address = $address;
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

//    /**
//     * @return Customer|null
//     */
//    public function getCustomer(): ?Customer
//    {
//        return $this->customer;
//    }
//
//    /**
//     * @var Customer|null $customer
//     */
//    public function setCustomer(?Customer $customer): void
//    {
//        $this->customer = $customer;
//    }



    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @var string $phone
     */
    public function setPhone($phone)
    {
        $utils = new GeneralUtils();
        $this->phone = $utils->formatPhoneNumber($phone);
    }


}
