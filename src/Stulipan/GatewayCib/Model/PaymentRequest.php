<?php


namespace App\Stulipan\GatewayCib\Model;

use App\Stulipan\GatewayCib\Model\BaseRequestModel;
use App\Stulipan\GatewayCib\Model\Enumerations\CibCurrency;
use App\Stulipan\GatewayCib\Model\Enumerations\CibLocale;


class PaymentRequest extends BaseRequestModel
{
    /**
     * @var array
     */
    public $args;

    public function __construct(string $uid = null, string $language = CibLocale::HU, float $amount = null, string $currency = CibCurrency::HUF, string $returnUrl = '')
    {
        parent::__construct();
        $this->msgt = 10;
        $this->transactionId = $this->generateTransactionId();
        $this->uid = $uid;
        $this->language = $language;
        $this->timestamp = date( 'YmdHis' );
        $this->authorization = 0;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->urlReturn = $returnUrl;

        //'PID' => WC_Gateway_CIB::$merchant_id,
        //'CRYPTO' => 1,
        //'MSGT' => 10,
        //'TRID' => $this->tr_id(),
        //'UID' => 'CIB12345678',
        //'LANG' => 'HU',
        //'TS' => date( 'YmdHis' ),
        //'AUTH' => 0,
        //'AMO' => $order->get_total(),
        //'CUR' => 'HUF',
        //'URL' => $this->returnUrl,
    }

    public function getArgs()
    {
        return $args = [
            'PID' => $this->pid,
            'CRYPTO' => $this->crypto,
            'MSGT' => $this->msgt,
            'TRID' => $this->transactionId,
            'UID' => $this->uid,
            'LANG' => $this->language,
            'TS' => $this->timestamp,
            'AUTH' => $this->authorization,
            'AMO' => $this->amount,
            'CUR' => $this->currency,
            'URL' => $this->urlReturn,
        ];
    }

    public function getRequestQuery()
    {
        $requestQuery = http_build_query($this->getArgs());
        return $requestQuery;
    }

    private function generateTransactionId() {
        $rnd = '';
        for ( $i = 1; $i <= 16; $i++ ) {
            if ( $i == 1 ) {
                $num = mt_rand( 1, 9 );
            } else {
                $num = mt_rand( 0, 9 );
            }

            $rnd .= $num;
        }
        return $rnd;
    }
}