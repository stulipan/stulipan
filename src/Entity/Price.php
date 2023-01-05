<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="price")
 * @ORM\Entity()
 * @ qORM\HasLifecycleCallbacks()
 */
class Price //implements \JsonSerializable
{
    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"productView", "productList",
     *     "geoPriceView", "geoPriceList"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var float
     * @Groups({
     *     "productView",
     *     "productList",
     *     "eventAddToCart",
     *     "geoPriceView",
     *     "geoPriceList"
     * })
     *
     * @ORM\Column(name="value", type="decimal", precision=10, scale=2, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Range(min=0.0001, minMessage="Az összeg nem lehet nulla vagy negatív.")
     */
    private $numericValue;

    /**
     * @var float|null
     * @Groups({
     *     "productView",
     *     "productList",
     *     "eventAddToCart"
     * })
     *
     * @ORM\Column(name="compare_at_value", type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\Range(min=0.0001, minMessage="Az összeg nem lehet nulla vagy negatív.")
     */
    private $compareAtValue;

    /**
     * @var VatRate
     * @ Groups({""})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VatRate")
     * @ORM\JoinColumn(name="vat_rate_id", referencedColumnName="id")
     * @ Assert\NotBlank(message="Válassz egy ÁFA típust ehhez az árhoz.")
     */
    private $vatRate;

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
            'value'     => $this->getNumericValue(),
            'vatRate'   => $this->getVatRate(),
        ];
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

//    public function __toString(): string
//    {
//        return (string) $this->getGrossPrice();
//    }

//    /**
//     * @return Product
//     */
//    public function getProduct(): ?Product
//    {
//        return $this->product;
//    }
//
//    /**
//     * @param Product $product
//     */
//    public function setProduct(?Product $product)
//    {
//        $this->product = $product;
//    }

    /**
     * @return float
     */
    public function getGrossPrice(): ?float
    {
        return (float) $this->numericValue;
    }

    /**
     * @param float $grossPrice
     */
    public function setGrossPrice(?float $grossPrice)
    {
        $this->numericValue = $grossPrice;
    }

    /**
     * @return float
     */
    public function getNumericValue(): ?float
    {
        return (float) $this->numericValue;
    }
    
    /**
     * @param float $value
     */
    public function setNumericValue(?float $value)
    {
        $this->numericValue = $value;
    }

    /**
     * @return float|null
     */
    public function getCompareAtValue(): ?float
    {
        return null == $this->compareAtValue ? null : (float) $this->compareAtValue;
    }

    /**
     * @param float|null $compareAtValue
     */
    public function setCompareAtValue($compareAtValue): void
    {
        $this->compareAtValue = $compareAtValue;
    }

    /**
     * !! This is to retrieve the active price (value).
     * @return float|null
     */
    public function getSellingPrice()
    {
        return $this->getNumericValue();
    }

    /**
     * @return VatRate
     */
    public function getVatRate(): ?VatRate
    {
//        if (!$this->vatRate) {
//            return $em->find(VatRate::class, VatRate::DEFAULT_VAT_RATE);
//        }
        return $this->vatRate;
    }

    /**
     * @param VatRate $vatRate
     */
    public function setVatRate(?VatRate $vatRate)
    {
//        dd($vatRate);
//        if (!$vatRate) {
//            $this->vatRate = $em->find(VatRate::class, VatRate::DEFAULT_VAT_RATE);
//        }
        $this->vatRate = $vatRate;
    }
    
}
