<?php

namespace App\Entity;

use App\Model\Locale;
use App\Repository\HostRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;

/**
 * @ORM\Table(name="host")
 * @ORM\Entity(repositoryClass=HostRepository::class)
 */
class Host
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $languageCode;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $countryCode;

    /**
     * @ORM\Column(name="enabled", type="boolean", options={"default"=true})
     */
    private $enabled = true;

    /**
     * @var Locale|null
     */
    private $locale;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(string $languageCode): self
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

//    /**
//     * Returns true or false, after transformation (1 or 0 which are stored in db)
//     * @return bool
//     */
//    public function isEnabled(): bool
//    {
//        return null === $this->enabled ? false : $this->enabled;
//    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Locale|null
     */
    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    /**
     * @param Locale|null $locale
     */
    public function setLocale(?Locale $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocaleCode(): ?string
    {
        return $this->languageCode.'_'.$this->getCountryCode();
    }
}
