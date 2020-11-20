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
use App\Validator\Constraints as CustomAssert;

/**
 *
 * @ORM\Table(name="sender")
 * @ORM\Entity
 *
 * @CustomAssert\BillingCompanyIsValid()
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
     * @ Assert\NotBlank(message="Egy címzetnek kell legyen egy címe.")
     * @Assert\Valid()
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=false)
     * @Assert\NotNull(message="Add meg a keresztnevet.")
     */
    private $firstname='';

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=false)
     * @Assert\NotNull(message="Add meg a vezetéknevet.")
     */
    private $lastname='';

    /**
     * @var string|null
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company='';

    /**
     * @var string|null
     *
     * @ORM\Column(name="company_vat_number", type="string", length=255, nullable=true)
     */
    private $companyVatNumber='';

//    /**
//     * @var int
//     *
//     * @ORM\Column(name="phone", type="string", length=15, nullable=false)
//     * @Assert\NotBlank(message="Add meg a telefonszámot.")
//     */
//    private $phone;

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
     * @return string|null
     */
    public function getCompanyVatNumber(): ?string
    {
        return $this->companyVatNumber;
    }

    /**
     * @param string|null $companyVatNumber
     */
    public function setCompanyVatNumber(?string $companyVatNumber): void
    {
        $this->companyVatNumber = $companyVatNumber;
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

//    /**
//     * @return string
//     */
//    public function getPhone(): ?string
//    {
//        return $this->phone;
//    }
//
//    /**
//     * @var string $phone
//     */
//    public function setPhone(?string $phone)
//    {
//        $utils = new GeneralUtils();
//        $this->phone = $utils->formatPhoneNumber($phone);
//    }

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
