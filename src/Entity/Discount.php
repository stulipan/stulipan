<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//* @MappedSuperclass
/**
 *
 * @ORM\Table(name="cart_discount")
 * @ORM\Entity
 */

class Discount
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @Assert\NotBlank(message="Adj nevet a promóciónak!")
     * @ORM\Column(name="discount_name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(message="Nem adtál meg kuponkódot.")
     * @ORM\Column(name="coupon_code", type="string", length=50, nullable=false)
     */
    private $couponCode;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="gross_price", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $discount;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCouponCode(): string
    {
        return $this->couponCode;
    }

    /**
     * @param string couponCode
     */
    public function setCouponCode(string $couponCode): void
    {
        $this->couponCode = $couponCode;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }
}