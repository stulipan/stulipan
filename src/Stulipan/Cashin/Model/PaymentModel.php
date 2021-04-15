<?php


namespace App\Stulipan\Cashin\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PaymentModel
{
    /**
     * @var mixed|null
     */
    private $payment;

    /**
     * @var string|null
     * @Assert\Length(max: 255)
     */
    private $errorRoute;

    /**
     * @var string|null
     * @Assert\Length(max: 255)
     * @Assert\Regex('http[s]?://.+\..+/')
     */
    private $paymentPageUrl;



    /**
     * @return mixed|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param mixed|null $payment
     */
    public function setPayment($payment): void
    {
        $this->payment = $payment;
    }

    public function isCreated(): bool
    {
        if ($this->payment !== null) {
            return true;
        }
        return false;
    }

    /**
     * @return string|null
     */
    public function getErrorRoute(): ?string
    {
        return $this->errorRoute;
    }

    /**
     * @param string|null $errorRoute
     */
    public function setErrorRoute(?string $errorRoute): void
    {
        $this->errorRoute = $errorRoute;
    }

    /**
     * @return string|null
     */
    public function getPaymentPageUrl(): ?string
    {
        return $this->paymentPageUrl;
    }

    /**
     * @param string|null $paymentPageUrl
     */
    public function setPaymentPageUrl(?string $paymentPageUrl): void
    {
        $this->paymentPageUrl = $paymentPageUrl;
    }

}