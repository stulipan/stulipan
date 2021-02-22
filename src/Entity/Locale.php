<?php

namespace App\Entity;


final class Locale
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $currencySymbol;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var string
     */
    private $timeFormat;


    public function __construct(string $code, string $name, string $currency, string $currencyCode, string $currencySymbol,
                                string $dateFormat, string $timeFormat)
    {
        $this->code = $code;
        $this->name = $name;
        $this->currency = $currency;
        $this->currencyCode = $currencyCode;
        $this->currencySymbol = $currencySymbol;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
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
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol;
    }

    /**
     * @param string $currencySymbol
     */
    public function setCurrencySymbol(string $currencySymbol): void
    {
        $this->currencySymbol = $currencySymbol;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat(string $dateFormat): void
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @return string
     */
    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    /**
     * @param string $timeFormat
     */
    public function setTimeFormat(string $timeFormat): void
    {
        $this->timeFormat = $timeFormat;
    }

}