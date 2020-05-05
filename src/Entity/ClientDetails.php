<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="client_details")
 * @ORM\Entity
 *  (repositoryClass="App\Repository\UserRepository")
 */
class ClientDetails
{
//    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="browser_ip", type="string", length=15, nullable=true)
     */
    private $browserIp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="user_agent", type="text", length=65535, nullable=true)
     */
    private $userAgent;

    /**
     * @var string|null
     *
     * @ORM\Column(name="accept_language", type="string", length=255, nullable=true)
     */
    private $acceptLanguage;

    public function __construct(?string $browserIp, ?string $userAgent, ?string $acceptLanguage)
    {
        $this->browserIp = $browserIp;
        $this->userAgent = $userAgent;
        $this->acceptLanguage = $acceptLanguage;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getBrowserIp(): ?string
    {
        return $this->browserIp;
    }

    /**
     * @param string|null $browserIp
     */
    public function setBrowserIp(?string $browserIp): void
    {
        $this->browserIp = $browserIp;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @param string|null $userAgent
     */
    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string|null
     */
    public function getAcceptLanguage(): ?string
    {
        return $this->acceptLanguage;
    }

    /**
     * @param string|null $acceptLanguage
     */
    public function setAcceptLanguage(?string $acceptLanguage): void
    {
        $this->acceptLanguage = $acceptLanguage;
    }
}