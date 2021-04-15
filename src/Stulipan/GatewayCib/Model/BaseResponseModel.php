<?php

namespace App\Stulipan\GatewayCib\Model;

use Symfony\Component\Validator\Constraints as Assert;

class BaseResponseModel
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
     * @Assert\Length(min: 2, max: 2)
     * @Assert\Regex('[0-9]{2}')
     */
    public $msgt;

    /**
     * @var string
     * @Assert\Length(min: 16, max: 16)
     * @Assert\Regex('[0-9]{16}')
     */
    public $transactionId;

    /**
     * @var string
     * @Assert\Length(min: 2, max: 2)
     * @Assert\Regex('[A-Z0-9]{2}')
     */
    public $responseCode;

    /**
     * @var string
     * @Assert\Length(max: 255)
     * @Assert\Regex('[.]{0,255}')
     */
    public $responseText;

//    public $errors;
    public $requestSuccessful;

    function __construct()
    {
        $this->crypto = 1;
//        $this->errors = array();
        $this->requestSuccessful = false;
    }

    public function fromJson($json)
    {
        if (!empty($json)) {
            $this->requestSuccessful = true;
//            if (!array_key_exists('errors', $json) || !empty($json['errors'])) {
//                $this->requestSuccessful = false;
//            }
//
//            if (array_key_exists('errors', $json)) {
//                foreach ($json['errors'] as $error) {
//                    $apiError = new ApiErrorModel();
//                    $apiError->fromJson($error);
//                    array_push($this->errors, $apiError);
//                }
//            } else {
//                $internalError = new ApiErrorModel();
//                $internalError->errorCode = "500";
//                if (array_key_exists('ExceptionMessage', $json)) {
//                    $internalError->title = $json['ExceptionMessage'];
//                    $internalError->description = $json['ExceptionType'];
//                } else {
//                    $internalError->title = "Internal Server Error";
//                }
//
//                array_push($this->errors, $internalError);
//            }
        }
    }
}