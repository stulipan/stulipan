<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="recipient")
 * @ORM\Entity
 */
class Recipient
{

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
     */
    private $address;

    /**
     * @var User
     *
     * ==== Many Recipients belong to one Customer ====
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * @ Assert\NotBlank(message="Egy customernek van egy címzetje, azaz egy Recipient.")
     */
    private $customer;


    /**
     * @var int
     *
     * @ORM\Column(name="phone", type="integer", nullable=false)
     */
    private $phone;



    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return int
     */
    public function getPhone(): ?int
    {
        return $this->phone;
    }

    /**
     * @var int $phone
     */
    public function setPhone(?int $phone)
    {
        $this->phone = $phone;
    }


}
