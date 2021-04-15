<?php


namespace App\Stulipan\GatewayCib\Model;

use App\Stulipan\GatewayCib\Model\BaseRequestModel;

class PaymentResponse extends BaseResponseModel
{
    public const SUCCESSFUL_INITIALIZATION = "00";
    public const INITIALIZATION_FAILED = "01";  // Initialization failed due to other technical reasons
    public const TRANSACTION_ID_IS_TAKEN = "02"; // The TRID is taken

    public $paymentRedirectUrl;

    public function __construct()
    {
        parent::__construct();
        $this->msgt = '';
        $this->transactionId = '';
        $this->responseCode = '';
    }

    public function fromJson($json)
    {
        if (!empty($json)) {
            parent::fromJson($json);
            $this->msgt = jget($json, 'MSGT');
            $this->pid = jget($json, 'PID');
            $this->transactionId = jget($json, 'TRID');
            $this->responseCode = jget($json, 'RC');
        }

//        if ($this->responseCode == self::SUCCESSFUL_INITIALIZATION) {
//            $this->requestSuccessful = true;
//        }
    }
}