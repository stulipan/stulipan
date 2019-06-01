<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PhoneNumber extends Constraint
{
//    public $message = 'A beírt telefonszám ("{{ string }}") hibás. A telefonszámot így írd be: +36xxxxxxxxx vagy 06xxxxxxxxx';
    public $message = 'Hibás telefonszám! Ellenőrizd, hogy helyesen írtad be. Elfogadott formátum: +36... vagy 06...';
    public $regionCode;

    /**
     * Define a parameter for this constraint. It then can be used like this:
     *      new PhoneNumber(['regionCode' => 'HU'])
     */
    public function getDefaultOption()
    {
        return 'regionCode';
    }

    /**
     * With the use of this function, you can make $regionCode a required parameter for this constraint
     */
    public function getRequiredOptions()
    {
//        return array('regionCode');
    }

}