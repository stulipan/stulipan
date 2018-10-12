<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//* @MappedSuperclass
/**

 *
 * @ORM\Table(name="cart_payment")
 * @ORM\Entity
 */

class Payment
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="payment_id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Adj nevet a fizetésnek.")
     * @ORM\Column(name="payment_name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $price;

    /**
     * @return string
     */
    public function getId(): ?string
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
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}