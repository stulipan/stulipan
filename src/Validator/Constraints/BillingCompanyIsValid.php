<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BillingCompanyIsValid extends Constraint
{
    public $message = '"{{ string }}"';
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

