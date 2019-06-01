<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="vat_rate_value")
 */
class VatValue implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint", name="id", length=11, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var VatRate
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VatRate", inversedBy="values")
     * @ORM\JoinColumn(name="vat_rate_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Válassz egy ÁFA típust.")
     */
    private $vatRate;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Az ÁFA nem lehet negatív.")
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    private $expiresAt;
    
    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $active;
    
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
            'value'     => $this->getValue(),
            'expiresAt' => $this->getExpiresAt(),
        ];
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    

    /**
     * @return VatRate
     */
    public function getVatRate(): ?VatRate
    {
        return $this->vatRate;
    }

    /**
     * @param VatRate $vatRate
     */
    public function setVatRate(?VatRate $vatRate)
    {
        $this->vatRate = $vatRate;
    }

    /**
     * @return float
     */
    public function getValue(): ?float
    {
        return (float) $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue(?float $value)
    {
        $this->value = $value;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime $expiresAt
     */
    public function setExpiresAt(?\DateTime $expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }
    
    /**
     * @return bool|null
     */
    public function isActive(): ?bool
    {
        return $this->active;
    }
    
    /**
     * @param bool $active
     */
    public function setActive(?bool $active)
    {
        $this->active = $active;
    }

}