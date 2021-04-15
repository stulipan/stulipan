<?php

namespace App\Stulipan\GatewayCib\Model;

class ApiErrorModel
{
    public $errorCode;
    public $title;
    public $description;

    function __construct()
    {
        $this->errorCode = "";
        $this->title = "";
        $this->description = "";
    }

    public function fromJson($json)
    {
        if (!empty($json)) {
            $this->errorCode = $json['errorCode'];
            $this->title = $json['title'];
            $this->description = $json['description'];
        }
    }
}