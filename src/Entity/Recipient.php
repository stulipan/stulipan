<?php

declare(strict_types=1);

namespace App\Entity;

use App\Controller\Utils\GeneralUtils;
use App\Entity\TimestampableTrait;
use App\Entity\User;

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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Add meg a címzett nevét.")
     */
    private $name='';

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
     * @var User
     *
     * ==== Many Recipients belong to one Customer ====
     * ==== inversed By="recipients" => a User entitásban definiált 'recipients' attibútumról van szó; A Címzettet így kötjük vissza a Customerhez
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="recipients")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ Assert\NotBlank(message="Egy címzetnek kell legyen felhasználója/Customer.")
     */
    private $customer;


    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15, nullable=false)
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
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
        return $this->getName();
    }

    /**
     * @return string|null
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
