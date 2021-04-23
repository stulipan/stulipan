<?php

namespace App\Validator\Constraints;
use App\Entity\Price;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceExistValidator extends ConstraintValidator
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PriceExist) {
            throw new UnexpectedTypeException($constraint, PriceExist::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Price) {
            return;
        }

        $errors = $this->validator->validate($value);

        if (count($errors) > 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}