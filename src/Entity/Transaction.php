<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\TimestampableTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_order_transaction")
 * @ORM\Entity()
 *     repositoryClass="App\Repository\OrderTransactionRepository")
 */
class Transaction
{
    public const KIND_AUTHORIZATION = 'authorization';
    public const KIND_CAPTURE       = 'capture';
    public const KIND_SALE          = 'sale';
    public const KIND_VOID          = 'void';
    public const KIND_REFUND        = 'refund';

    public const ERROR_INCORRECT_NUMBER      = 'incorrect_number';
    public const ERROR_INVALID_NUMBER        = 'invalid_number';
    public const ERROR_INVALID_EXPIRY_DATE   = 'invalid_expiry_date';
    public const ERROR_INVALID_CVC           = 'invalid_cvc';
    public const ERROR_EXPIRED_CARD          = 'expired_card';
    public const ERROR_INCORRECT_CVC         = 'incorrect_cvc';
    public const ERROR_INCORRECT_ZIP         = 'incorrect_zip';
    public const ERROR_INCORRECT_ADDRESS     = 'incorrect_address';
    public const ERROR_CARD_DECLINED         = 'card_declined';
    public const ERROR_PROCESSING_ERROR      = 'processing_error';
    public const ERROR_CALL_ISSUER           = 'call_issuer';
    public const ERROR_PICK_UP_CARD          = 'pick_up_card';

    public const STATUS_PENDING      = 'pending';
    public const STATUS_FAILURE      = 'failure';
    public const STATUS_ERROR        = 'error';
    public const STATUS_SUCCESS      = 'success';

    public const SOURCE_WEB         = 'web';
    public const SOURCE_POS         = 'pos';


    use TimestampableTrait;

    /**
     * @var int
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var float
     * @Assert\NotBlank()
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false, options={"default":0})
     */
    private $amount = 0;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="currency", type="string", length=3, nullable=false)
     */
    private $currency;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="kind", type="string", length=10, nullable=false)
     */
    private $kind;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="authorization", type="string", length=255, nullable=true)
     */
    private $authorization;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="error_code", type="string", length=255, nullable=true)
     */
    private $errorCode;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="gateway", type="string", length=255, nullable=true)
     */
    private $gateway;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Hiányzik a vásárló keresztneve!")
     */
    private $message;

    /**
     * @var Order|null
     * @Groups({"orderView"})
     *
     * ==== Many Transactions belong to one Order ====
     * ==== inversed By="transactions" => a Order entitásban definiált 'transactions' attibútumról van szó; A Transactiont így kötjük vissza az Orderhez
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="transactions")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message="Egy tranzakcióhoz párosulnia kell egy rendelés.")
     */
    private $order;

    /**
     * @var Transaction|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Transaction")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="processed_at", type="datetime", nullable=true)
     *
     */
    protected $processedAt;

    /**
     * @var string|null
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="source_name", type="string", length=255, nullable=true)
     */
    private $source_name;

    /**
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status;

    /**
     * @var bool
     * @Groups({"productView"})
     *
     * @ORM\Column(name="is_test", type="boolean", nullable=false, options={"default"=false})
     */
    private $test = 0;



    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @param string $kind
     */
    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }

    /**
     * @return string|null
     */
    public function getAuthorization(): ?string
    {
        return $this->authorization;
    }

    /**
     * @param string|null $authorization
     */
    public function setAuthorization(?string $authorization): void
    {
        $this->authorization = $authorization;
    }

    /**
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @param string|null $errorCode
     */
    public function setErrorCode(?string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string|null
     */
    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    /**
     * @param string|null $gateway
     */
    public function setGateway(?string $gateway): void
    {
        $this->gateway = $gateway;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order|null $order
     */
    public function setOrder(?Order $order): void
    {
        $this->order = $order;
    }

    /**
     * @return Transaction|null
     */
    public function getParent(): ?Transaction
    {
        return $this->parent;
    }

    /**
     * @param Transaction|null $parent
     */
    public function setParent(?Transaction $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return DateTime|null
     */
    public function getProcessedAt(): ?DateTime
    {
        return $this->processedAt;
    }

    /**
     * @param DateTime|null $processedAt
     */
    public function setProcessedAt(?DateTime $processedAt): void
    {
        $this->processedAt = $processedAt;
    }

    /**
     * @return string|null
     */
    public function getSourceName(): ?string
    {
        return $this->source_name;
    }

    /**
     * @param string|null $source_name
     */
    public function setSourceName(?string $source_name): void
    {
        $this->source_name = $source_name;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isTest(): bool
    {
        return null === $this->test ? false : $this->test;
    }

    /**
     * @param bool $test
     */
    public function setTest(bool $test): void
    {
        $this->test = $test;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return null === $this->errorCode ? false : true;
    }
}
