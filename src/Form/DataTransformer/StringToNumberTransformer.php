<?php

namespace App\Form\DataTransformer;

use App\Validator\Constraints as AssertApp;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StringToNumberTransformer implements DataTransformerInterface
{

    //////// NINCS HASZNALATBAN ////////
    //////// Amikor meg int -kent taroltam a telszamot db-ben akkor kellett ez az atalakito ////////

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Transforms a number to a string
     *
     * @param  int|null $number
     * @return string
     */
    public function transform($number)
    {
        if (null === $number) {
            return '';
        }

        return (string) $number;
    }

    /**
     * Transforms a string to a number
     *
     * @param  string $string
     * @return int|null
     */
    public function reverseTransform($string)
    {
        // no issue number? It's optional, so that's ok
        if (!$string) {
            return;
        }

        $phoneConstraint = new AssertApp\PhoneNumber(['regionCode' => 'HU']);
        // all constraint "options" can be set this way
//        $phoneConstraint->message = 'Invalid email address';

        $errors = $this->validator->validate($string, $phoneConstraint);

        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneProto = $phoneUtil->parse($string, "HU");
        } catch (\libphonenumber\NumberParseException $e) {
            return;
        }

        if (0 === count($errors)) {
            return (int) $phoneProto->getCountryCode().$phoneProto->getNationalNumber();

        } else {
            // this is *not* a valid email address
            $errorMessage = $errors[0]->getMessage();
//            dd($errorMessage);
//            throw new TransformationFailedException(
//                sprintf('A "%s" string-et nem lehet számmá alakítani!', $string));
            return;
        }

//        if (!is_numeric((int) $string))  {
//            // causes a validation error
//            // this message is not shown to the user
//            // see the invalid_message option in the form type
//            throw new TransformationFailedException(
//                sprintf('A "%s" string-et nem lehet számmá alakítani!', $string));
//        }

//        return (int) $string;
    }
}