<?php

namespace App\Stulipan\Cashin\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CashinErrorModel
{
    public const ERR_INCORRECT_NUMBER = 'incorrect_number';
    public const ERR_INVALID_NUMBER = 'invalid_number';
    public const ERR_INVALID_EXPIRY_DATE = 'invalid_expiry_date';
    public const ERR_INVALID_CVC = 'invalid_cvc';
    public const ERR_EXPIRED_CARD = 'expired_card';
    public const ERR_INCORRECT_CVC = 'incorrect_cvc';
    public const ERR_INCORRECT_ZIP = 'incorrect_zip';
    public const ERR_INCORRECT_ADDRESS = 'incorrect_address';
    public const ERR_CARD_DECLINED = 'card_declined';
    public const ERR_PROCESSING_ERROR = 'processing_error';
    public const ERR_CALL_ISSUER = 'call_issuer';
    public const ERR_PICK_UP_CARD = 'pick_up_card';

    /**
     * @var string|null
     * @Assert\Length(max: 255)
     */
    private $code;

    /**
     * @var string|null
     * @Assert\Length(max: 255)
     */
    private $message;

    /**
     * PaymentErrorModel constructor.
     * @param string|null $code
     * @param string|null $message
     */
    public function __construct(?string $code, ?string $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
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
}