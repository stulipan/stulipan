<?php


namespace App\Stulipan\GatewayCib\Model;

use App\Stulipan\GatewayCib\Model\BaseRequestModel;
use App\Stulipan\GatewayCib\Model\Enumerations\CibCurrency;
use App\Stulipan\GatewayCib\Model\Enumerations\CibLocale;


class PaymentStatusResponse extends BaseResponseModel
{
    public const SUCCESSFUL_AUTHORIZATION = "00";
    public const AUTHORIZATION_NOT_TAKEN_PLACE_YET = "PR";  // Authorisation has not taken place yet
    public const TRANSACTION_TIME_OUT = "TO"; // Transaction failed due to time-out
    public const AUTHORIZATION_FAILED = "";  // Authorisation failed (error code classification is available in the FAQ section)

    public $amount;
    public $authorizationNumber;
    public $cardNumber;

    public function __construct()
    {
        parent::__construct();
        $this->msgt = '';
        $this->transactionId = '';
        $this->amount = '';
        $this->responseCode = '';
        $this->responseText = '';
        $this->authorizationNumber = '';
        $this->cardNumber = '';
    }

    public function fromJson($json)
    {
        if (!empty($json)) {
            parent::fromJson($json);
            $this->msgt = jget($json, 'MSGT');
            $this->pid = jget($json, 'PID');
            $this->transactionId = jget($json, 'TRID');
            $this->amount = jget($json, 'AMO');
            $this->responseCode = jget($json, 'RC');
            $this->responseText = jget($json, 'RT');
            $this->authorizationNumber = jget($json, 'ANUM');
            $this->cardNumber = jget($json, 'CNUM');
        }

//        if ($this->responseCode == self::SUCCESSFUL_INITIALIZATION) {
//            $this->requestSuccessful = true;
//        }
    }
}