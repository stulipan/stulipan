<?php

namespace App\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * NINCS HASZNALATBAN
 * A MessageType subformban oldalom meg a validaciot!
 *
 */
class MessageWithAuthorValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MessageWithAuthor) {
            throw new UnexpectedTypeException($constraint, MessageWithAuthor::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if ( (!$value->getMessage() && !$value->getMessageAuthor()) || ($value->getMessage() && $value->getMessageAuthor()) ) {
            return;
        } else {
            if (!$value->getMessage()) {
                $this->context->buildViolation('Az üzenet lemaradt!')
//                    ->setParameter('{{ string }}', 'Az üzenet lemaradt!')
                    ->addViolation();
            }
            if (!$value->getMessageAuthor()) {
                $this->context->buildViolation('Nem írtad alá az üzenetet')
//                    ->setParameter('{{ string }}', 'Nem írtad alá az üzenetet')
                    ->addViolation();
            }
        }

//        try {
//            $phoneProto = $phoneUtil->parse($value, $constraint->regionCode);
//            if (!$phoneUtil->isValidNumberForRegion($phoneProto, $constraint->regionCode)) {
//                $this->context->buildViolation($constraint->message)
//                    ->setParameter('{{ string }}', $value)
//                    ->addViolation();
//            }
//        } catch (NumberParseException $e) {
//            $this->context->buildViolation($constraint->message)
//                ->setParameter('{{ string }}', $value)
//                ->addViolation();
//        }
    }
}