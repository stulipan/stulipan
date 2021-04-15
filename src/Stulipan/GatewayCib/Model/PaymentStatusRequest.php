<?php


namespace App\Stulipan\GatewayCib\Model;

use App\Stulipan\GatewayCib\Model\BaseRequestModel;
use App\Stulipan\GatewayCib\Model\Enumerations\CibCurrency;
use App\Stulipan\GatewayCib\Model\Enumerations\CibLocale;


class PaymentStatusRequest extends BaseRequestModel
{
    public function __construct(string $msgt = null, string $pid = null, string $transactionId = null, float $amount = null)
    {
        parent::__construct();
        $this->msgt = $msgt;
        $this->pid = $pid;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
    }

    public function getQueryString()
    {
        $query = http_build_query([
            'PID' => $this->pid,
//            'CRYPTO' => $this->crypto,
            'MSGT' => $this->msgt,
            'TRID' => $this->transactionId,
            'AMO' => $this->amount,
        ]);
        return $query;
    }
}