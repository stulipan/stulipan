<?php

namespace App\Validator\Constraints;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PhoneNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PhoneNumber) {
            throw new UnexpectedTypeException($constraint, PhoneNumber::class);
        }

        /**
         * 'regionCode' is a parameter defined in PhoneNumber constraint
         * If missing, set default value to 'HU'
         */
        if (null === $constraint->regionCode || '' === $constraint->regionCode) {
            $constraint->regionCode = 'HU';
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneProto = $phoneUtil->parse($value, $constraint->regionCode);
            if (!$phoneUtil->isValidNumberForRegion($phoneProto, $constraint->regionCode)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value)
                    ->addViolation();
            }
        } catch (NumberParseException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}