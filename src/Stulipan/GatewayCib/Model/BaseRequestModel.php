<?php

namespace App\Stulipan\GatewayCib\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mother of all requests. For now with every request, the  credentials have to be sent as well.
 * This will be changed to oAuth in the near future.
 */
class BaseRequestModel
{
    /**
     * POS Id or Merchant Id
     *
     * @var string
     * @Assert\Length(min: 7, max: 7)
     * @Assert\Regex('[a-zA-Z]{3}[01][0-9]{3}')
     */
    public $pid;

    // descifer hash key
//    public $key;

    /**
     * @var string
     * @Assert\Length(min: 1, max: 1)
     * @Assert\Regex('[13]')
     */
    public $crypto;

    /**
     * @var string
     * @Assert\Length(min: 16, max: 16)
     * @Assert\Regex('[0-9]{16}')
     */
    public $transactionId;

    /**
     * @var string
     * @Assert\Length(min: 2, max: 2)
     * @Assert\Regex('[0-9]{2}')
     */
    public $msgt;

    /**
     * @var string
     * @Assert\Length(min: 11, max: 11)
     * @Assert\Regex('[a-zA-Z0-9\-_]{11}')
     */
    public $uid;

    /**
     * @var float
     * @Assert\Length(max: 16)
     * @Assert\Regex('[0-9 \.]{16}')
     */
    public $amount;

    /**
     * @var string
     * @Assert\Length(min: 3, max: 3)
     * @Assert\Regex('[a-zA-Z]{3}')
     */
    public $currency;


    /**
     * @var string
     * @Assert\Length(min: 14, max: 14)
     * @Assert\Regex('[0-9]{14}')
     */
    public $timestamp;


    /**
     * @var string
     * @Assert\Length(min: 1, max: 1)
     * @Assert\Regex('[0]')
     */
    public $authorization;

    /**
     * @var string
     * @Assert\Length(min: 2, max: 2)
     * @Assert\Regex('[a-zA-Z]{2}')
     */
    public $language;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('http[s]?://.+\..+/')
     */
    public $urlReturn;

    public $extra1; //

    /**
     * @var string
     * @Assert\Length(min: 2, max: 2)
     * @Assert\Regex('[a-zA-Z]{2}')
     */
    public $customerCountry;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('[0-9a-zA-Z\-]{0,255}')
     */
    public $customerState;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('[a-zA-Z0-9\%]{0,255}')
     */
    public $customerCity;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('[0-9a-zA-Z]{0,10}')
     */
    public $customerPostal;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('[a-zA-Z0-9\%]{0,255}')
     */
    public $customerAddress;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('([a-zA-Z0-9_\.\+\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+)?')
     */
    public $customerEmail;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('[a-zA-Z0-9\%]{0,255}')
     */
    public $customerName;

    public function __construct()
    {
        $this->crypto = 1;
    }

}