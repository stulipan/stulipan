<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This is used in MessageAndCustomer::class
 *
 */
class CartCardIsValidValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CartCardIsValid) {
            throw new UnexpectedTypeException($constraint, CartCardIsValid::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if ( (!$value->getMessage() && !$value->getAuthor()) || ($value->getMessage() && $value->getAuthor()) ) {
            return;
        } else {
            if (!$value->getMessage()) {
                $this->context->buildViolation('Az üzenet lemaradt...')
                    ->atPath('message')
//                    ->setParameter('{{ string }}', 'Az üzenet lemaradt!')
                    ->addViolation();
            }
            if (!$value->getAuthor()) {
                $this->context->buildViolation('Nem írtad alá az üzenetet.')
                    ->atPath('author')
//                    ->setParameter('{{ string }}', 'Nem írtad alá az üzenetet')
                    ->addViolation();
            }
        }
    }
}