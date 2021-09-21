<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_order_funding_detail")
 * @ORM\Entity()
 */
class PaymentFundingDetail
{

    /**
     * @var int
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="credit_card_number", type="string", length=4, nullable=true)
     */
    private $creditCardNumber;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="credit_card_company", type="string", length=255, nullable=true)
     */
    private $creditCardCompany;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="expiry_year", type="string", length=4, nullable=true)
     */
    private $expiryYear;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="expiry_month", type="string", length=2, nullable=true)
     */
    private $expiryMonth;

//    /**
//     * @var Transaction|null
//     *
//     * @ORM\OneToOne(targetEntity="App\Entity\Transaction")
//     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
//     */
//    private $parent;



    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCreditCardNumber(): ?string
    {
        return $this->creditCardNumber;
    }

    /**
     * @param string|null $creditCardNumber
     */
    public function setCreditCardNumber(?string $creditCardNumber): void
    {
        $this->creditCardNumber = $creditCardNumber;
    }

    /**
     * @return string|null
     */
    public function getCreditCardCompany(): ?string
    {
        return $this->creditCardCompany;
    }

    /**
     * @param string|null $creditCardCompany
     */
    public function setCreditCardCompany(?string $creditCardCompany): void
    {
        $this->creditCardCompany = $creditCardCompany;
    }

    /**
     * @return string|null
     */
    public function getExpiryYear(): ?string
    {
        return $this->expiryYear;
    }

    /**
     * @param string|null $expiryYear
     */
    public function setExpiryYear(?string $expiryYear): void
    {
        $this->expiryYear = $expiryYear;
    }

    /**
     * @return string|null
     */
    public function getExpiryMonth(): ?string
    {
        return $this->expiryMonth;
    }

    /**
     * @param string|null $expiryMonth
     */
    public function setExpiryMonth(?string $expiryMonth): void
    {
        $this->expiryMonth = $expiryMonth;
    }

}
