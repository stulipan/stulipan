<?php

namespace App\Form\DataTransformer;

use App\Entity\Price;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

//      NINCS HASZNALATBAN !!!
class NumberToPriceTransformer implements DataTransformerInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms a Price object into a number (the actual value of the Price)
     *
     * @return string
     */
    public function transform($price)
    {
        if (null === $price) {
            return null;
        }
        return $price->getGrossPrice();
    }

    /**
     * Transforms a number into an object (Price)
     *
     * @param float $number
     * @return Price|null
     */
    public function reverseTransform($number)
    {
        // no issue number? It's optional, so that's ok
        if (!$number) {
            return;
        }

//        dd($intervalId);
        $price = new Price();
        $price->setGrossPrice($number);

        if (null === $price->getGrossPrice()) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'HIBA: nem tud Price objectet létrehozni: "%s"!',
                $number
            ));
        }

//        dd($price);
        return $price;
    }
}