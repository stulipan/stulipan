<?php

namespace App\Form\DataTransformer;

use App\Entity\Model\DeliveryDate;
use App\Validator\Constraints as AssertApp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeliveryDateToStringTransformer implements DataTransformerInterface
{

    /**
     * Transforms a DeliveryDate (\Datetime) to a string
     *
     * @return string
     */
    public function transform($date)
    {
        if (null === $date) {
            return '';
        }

        return $date->format('Y-m-d');
    }

    /**
     * Transforms a string to a DeliveryDate (\Datetime) object
     *
     * @param  string $stringDate
     * @return DeliveryDate|null
     */
    public function reverseTransform($stringDate)
    {
        // no issue number? It's optional, so that's ok
        if (!$stringDate) {
            return;
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $stringDate);

        if (!$date instanceof \DateTime) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Hiba: ilyen dátumot nem létezik: "%s"!',
                $stringDate
            ));
        } else {
            return $date;
        }
    }
}