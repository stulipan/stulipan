<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Annotation
 */
class PhoneNumber extends Constraint
{
    public $regionCode;
    public $message;

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