<?php

namespace App\Stulipan\GatewayCib\Model\Enumerations;

use ReflectionClass;

abstract class CibCurrency
{
    const HUF = "HUF";
    const EUR = "EUR";

    public static function isValid($name)
    {
        $class = new ReflectionClass(__CLASS__);
        $constants = $class->getConstants();
        return array_key_exists($name, $constants);
    }
}