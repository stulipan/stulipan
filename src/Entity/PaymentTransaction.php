<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="cart_order_transaction")
 * @ORM\Entity()
 *     repositoryClass="App\Repository\OrderTransactionRepository")
 */
class PaymentTransaction
{
    public const KIND_AUTHORIZATION = 'authorization';  // An amount reserved against the cardholder's funding source. Money does not change hands until the authorization is captured.
    public const KIND_CAPTURE       = 'capture';        // A transfer of the money that was reserved during the authorization stage.
    public const KIND_SALE          = 'sale';           // An authorization and capture performed together in a single step.
    public const KIND_VOID          = 'void';           // A cancellation of a pending authorization or capture.
    public const KIND_REFUND        = 'refund';         // A partial or full return of captured funds to the cardholder. A refund can happen only after a capture is processed.

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

    public const STATUS_PENDING      = 'pending';   // amikor egy tranzakcio bejegyzes krealodik
    public const STATUS_SUCCESS      = 'success';
    public const STATUS_FAILURE      = 'failure';   // amikor egy tranzakcio nem sikerul (insufficient funds, card declined / expired, incorrect number, valid expiry, etc)
    public const STATUS_ERROR        = 'error';     // amikor tehnikai problema va (nem valaszol a gateway, hibas GW login info, etc)
    public const STATUS_CANCELED     = 'canceled';  // By Stulipan: a user feladja

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
     * @var string
     * @Groups({"orderView"})
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status;

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
     * @ORM\Column(name="gateway", type="string", length=255, nullable=true)
     */
    private $gateway;

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
     * @var PaymentTransaction|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\PaymentTransaction")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @var PaymentFundingDetail|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\PaymentFundingDetail")
     * @ORM\JoinColumn(name="funding_detail_id", referencedColumnName="id", nullable=true)
     */
    private $fundingDetail;

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
     * @var bool
     * @Groups({"productView"})
     *
     * @ORM\Column(name="is_test", type="boolean", nullable=false, options={"default"=false})
     */
    private $test = 0;


    /**
     * @var string|null
     *
     * @ORM\Column(name="gateway_error_code", type="string", length=255, nullable=true)
     */
    private $gatewayErrorCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gateway_error_title", type="string", length=255, nullable=true)
     */
    private $gatewayErrorTitle;


    /**
     * @var string|null
     *
     * @ORM\Column(name="gateway_error_message", type="string", length=255, nullable=true)
     * @ Assert\NotBlank(message="Hiányzik a vásárló keresztneve!")
     */
    private $gatewayErrorMessage;



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
     * @return PaymentTransaction|null
     */
    public function getParent(): ?PaymentTransaction
    {
        return $this->parent;
    }

    /**
     * @param PaymentTransaction|null $parent
     */
    public function setParent(?PaymentTransaction $parent): void
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

    /**
     * @return PaymentFundingDetail|null
     */
    public function getFundingDetail(): ?PaymentFundingDetail
    {
        return $this->fundingDetail;
    }

    /**
     * @param PaymentFundingDetail|null $fundingDetail
     */
    public function setFundingDetail(?PaymentFundingDetail $fundingDetail): void
    {
        $this->fundingDetail = $fundingDetail;
    }

    /**
     * @return string|null
     */
    public function getGatewayErrorCode(): ?string
    {
        return $this->gatewayErrorCode;
    }

    /**
     * @param string|null $gatewayErrorCode
     */
    public function setGatewayErrorCode(?string $gatewayErrorCode): void
    {
        $this->gatewayErrorCode = $gatewayErrorCode;
    }

    /**
     * @return string|null
     */
    public function getGatewayErrorTitle(): ?string
    {
        return $this->gatewayErrorTitle;
    }

    /**
     * @param string|null $gatewayErrorTitle
     */
    public function setGatewayErrorTitle(?string $gatewayErrorTitle): void
    {
        $this->gatewayErrorTitle = $gatewayErrorTitle;
    }

    /**
     * @return string|null
     */
    public function getGatewayErrorMessage(): ?string
    {
        return $this->gatewayErrorMessage;
    }

    /**
     * @param string|null $gatewayErrorMessage
     */
    public function setGatewayErrorMessage(?string $gatewayErrorMessage): void
    {
        $this->gatewayErrorMessage = $gatewayErrorMessage;
    }
}
