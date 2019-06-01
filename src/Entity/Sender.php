<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\TimestampableTrait;
use App\Entity\User;
use App\Entity\Address;

use App\Form\AddressType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="sender")
 * @ORM\Entity
 */
class Sender
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
     * @var User
     *
     * ==== Many Senders belong to one Customer ====
     * ==== inversed By="senders" => a User entitásban definiált 'senders' attibútumról van szó; A Sendert így kötjük vissza a Customerhez
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="senders")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ Assert\NotBlank(message="Egy számlázási címnek kell legyen felhasználója/Customer.")
     */
    private $customer;

    /**
     * @var Address
     *
     * ==== One Sender has one Address ====
     *
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy címzetnek kell legyen egy címe.")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name='';

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company='';

    /**
     * @var int
     *
     * @ORM\Column(name="phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
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
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @var string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @var string $company
     */
    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return User
     */
    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    /**
     * @var User $customer
     */
    public function setCustomer(?User $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @var string $phone
     */
    public function setPhone(?string $phone)
    {
        $utils = new GeneralUtils();
        $this->phone = $utils->formatPhoneNumber($phone);
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

}
