<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MessageWithAuthor extends Constraint
{
//    public $message = 'A beírt telefonszám ("{{ string }}") hibás. A telefonszámot így írd be: +36xxxxxxxxx vagy 06xxxxxxxxx';
    public $message = '("{{ string }}")';

}