<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PriceNotBlankIfIntervalExits extends Constraint
{
    public $message = 'Hiányzik az ár!';
}