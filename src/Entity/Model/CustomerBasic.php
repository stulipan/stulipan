<?php

declare(strict_types=1);

namespace App\Entity\Model;

use App\Controller\Utils\GeneralUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AssertApp;

/**
 *
 */

class CustomerBasic
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email(message="Ellenőrizd, hogy helyesen írtad be az email címet!")
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $firstname;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Add meg a telefonszámot.")
     * @AssertApp\PhoneNumber()
     */
    private $phone;

    public function __construct(string $email = null, string $firstname = null, string $lastname = null, string $phone = null)
    {
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getFullname(): ?string
    {
        $fullname = $this->firstname.' '.$this->lastname;
        return $fullname;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return (string) $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(?string $phone)
    {
        $utils = new GeneralUtils();
        $this->phone = $utils->formatPhoneNumber($phone);
    }



}