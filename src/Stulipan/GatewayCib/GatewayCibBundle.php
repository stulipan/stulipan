<?php

namespace App\Stulipan\GatewayCib;

DEFINE("CIB_MARKET_URL_PROD", "http://eki.cib.hu:8090/market.saki");
DEFINE("CIB_CUSTOMER_URL_PROD", "https://eki.cib.hu/customer.saki");
DEFINE("CIB_MARKET_URL_TEST", "http://ekit.cib.hu:8090/market.saki");
DEFINE("CIB_CUSTOMER_URL_TEST", "https://ekit.cib.hu/customer.saki");


use App\Stulipan\GatewayCib\Model\ApiErrorModel;
use App\Stulipan\GatewayCib\Model\BaseResponseModel;
use App\Stulipan\GatewayCib\Model\Enumerations\CibEnvironment;
use App\Stulipan\GatewayCib\Model\PaymentRequest;
use App\Stulipan\GatewayCib\Model\PaymentResponse;
use App\Stulipan\GatewayCib\Model\PaymentStatusRequest;
use App\Stulipan\GatewayCib\Model\PaymentStatusResponse;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GatewayCibBundle extends Bundle
{
    private $pid;
    private $des;
    private $environment;

    private $CIB_MARKET_URL = "";
    private $CIB_CUSTOMER_URL = "";

    /**
     *  Constructor
     *
     * @param string $pid The secret POSKey of your shop
     * @param string $env The environment to connect to
     */
    function __construct($pid = null, $des = null, $env = CibEnvironment::PROD)
    {
        $this->pid = $pid;
        $this->des = $des;
        $this->environment = $env;

        switch ($env) {

            case CibEnvironment::TEST:
                $this->CIB_MARKET_URL = CIB_MARKET_URL_TEST;
                $this->CIB_CUSTOMER_URL = CIB_CUSTOMER_URL_TEST;
                break;

            case CibEnvironment::PROD:
            default:
                $this->CIB_MARKET_URL = CIB_MARKET_URL_PROD;
                $this->CIB_CUSTOMER_URL = CIB_CUSTOMER_URL_PROD;
                break;
        }
    }

    /**
     * Prepare a new payment
     *
     * @param PaymentRequest $model The request model for payment preparation
     * @return PaymentResponse Returns the response from the Barion API
     */
    public function preparePayment(PaymentRequest $model): PaymentResponse
    {
        $model->pid = $this->pid;
        $query = $model->getRequestQuery();
        $encodedQuery = ekiEncode($query, $this->des);

//        $url = $this->CIB_MARKET_URL . '?' . $encodedQuery;
        $url = $this->CIB_MARKET_URL;
        $response = $this->postToCib($url, $encodedQuery);
        $response = ekiDecode($response, $this->des);

        $paymentResponse = new PaymentResponse();
        if (!empty($response)) {

//            $json = json_decode($response, true);
            parse_str($response, $json);
            $paymentResponse->fromJson($json);

            if ($paymentResponse->requestSuccessful && $paymentResponse->responseCode === PaymentResponse::SUCCESSFUL_INITIALIZATION) {
                $args = [
                    'PID' => $paymentResponse->pid,
                    'CRYPTO' => $paymentResponse->crypto,
                    'MSGT' => 20,
                    'TRID' => $paymentResponse->transactionId,
                ];
                $encodedQuery = ekiEncode(http_build_query($args), $this->des);
                $paymentResponse->paymentRedirectUrl = $this->CIB_CUSTOMER_URL . "?" . $encodedQuery;
            }
        }
//        dd($paymentResponse);
        return $paymentResponse;
    }

    public function preparePaymentStatus(PaymentStatusRequest $statusRequest): PaymentStatusResponse
    {
        $statusRequest->pid = $this->pid;
        $query = $statusRequest->getQueryString();
        $encodedQuery = ekiEncode($query, $this->des);

        $url = $this->CIB_MARKET_URL;
        $response = $this->postToCib($url, $encodedQuery);
//        dd($response);
        $response = ekiDecode($response, $this->des);

        $statusResponse = new PaymentStatusResponse();
        if (!empty($response)) {
//            dd($response);
            parse_str($response, $json);
//            dd($json);
            $statusResponse->fromJson($json);
        }
//        dd($statusResponse);
        return $statusResponse;
    }

    public function postToCib($url, $params)
    {
        $ch = curl_init();

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if ($userAgent == "") {
            $cver = curl_version();
            $userAgent = "curl/" . $cver["version"] . " " .$cver["ssl_version"];
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/plain", "User-Agent: $userAgent")); // ez akkor kell ha nem POST

        if(substr(phpversion(), 0, 3) < 5.6) {
            curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        }

//        if ($this->UseBundledRootCertificates) {
//            curl_setopt($ch, CURLOPT_CAINFO, join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'ssl', 'cacert.pem')));
//
//            if ($this->Environment == BarionEnvironment::Test) {
//                curl_setopt($ch, CURLOPT_CAPATH, join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'ssl', 'gd_bundle-g2.crt')));
//            }
//        }

        $output = curl_exec($ch);
        $outputStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($outputStatus != "200") {
            echo "HTTP STATUS ".$http_status.", EKI error: ".$http_response;
//            exit;
            $error = new ApiErrorModel();
            $error->errorCode = "EKI error";
            $error->title = "HTTP STATUS: " . $outputStatus;
            $error->description = $output;

            $response = new BaseResponseModel();
            $response->errors = array($error);
            $output = json_encode($response);

            return $output;
        }

        if ($err_nr = curl_errno($ch)) {
            $error = new ApiErrorModel();
            $error->errorCode = "CURL_ERROR";
            $error->title = "CURL Error #" . $err_nr;
            $error->description = curl_error($ch);

            $response = new BaseResponseModel();
            $response->errors = array($error);
            $output = json_encode($response);
        }
        curl_close($ch);

        return $output;
    }

//    public static function get_cib( $args, $from = 'market' ) {
//        self::get_request_url( self::$testmode );
//        $url = ( $from === 'market' ) ? self::$murl . '?' . $args : self::$curl . '?' . $args;
//        self::log( 'Get CIB	' . 'URL: ' . $url, 'info' );
//
//        $resp = wp_remote_get( $url );
//        if ( is_wp_error( $resp ) ) {
//            self::log( 'Get CIB	' . 'Response error: ', json_encode( $resp ), 'error' );
//            return;
//        }
//        $body = wp_remote_retrieve_body( $resp );
//        self::log( 'Get CIB	' . 'Response body: ' . $body, 'info' );
//        return $body;
//    }

}