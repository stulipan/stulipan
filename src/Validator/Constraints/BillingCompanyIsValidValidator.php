<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This is used in Sender::class
 *
 */
class BillingCompanyIsValidValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BillingCompanyIsValid) {
            throw new UnexpectedTypeException($constraint, BillingCompanyIsValid::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if ( (!$value->getCompany() && !$value->getCompanyVatNumber()) || ($value->getCompany() && $value->getCompanyVatNumber()) ) {
            return;
        } else {
            if (!$value->getCompany()) {
                $this->context->buildViolation('Add meg a cégnevet.')
                    ->atPath('company')
//                    ->setParameter('{{ string }}', 'A cégnév lemaradt!')
                    ->addViolation();
            }
            if (!$value->getCompanyVatNumber()) {
                $this->context->buildViolation('Add meg az ÁFA számot.')
                    ->atPath('companyVatNumber')
//                    ->setParameter('{{ string }}', 'Add meg az ÁFA számot.')
                    ->addViolation();
            }
        }
    }
}