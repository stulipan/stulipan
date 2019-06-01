<?php

namespace App\Form\DataTransformer;

use App\Entity\DeliveryDateInterval;
use App\Entity\Model\DeliveryDate;
use App\Validator\Constraints as AssertApp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeliveryIntervalToStringTransformer implements DataTransformerInterface
{


    /**
     * !!!!! NINCS HASZNALVA !!!!
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * Transforms a DeliveryDateInterval object to a string (the name of the interval, eg: 12-16)
     *
     * @return string
     */
    public function transform($interval)
    {
        if (null === $interval) {
            return '';
        }
        dd($interval);
        return $interval->getName();
    }

    /**
     * Transforms a number (interval id) into an object (DeliveryInterval)
     *
     * @param  int $intervalId
     * @return DeliveryDateInterval|null
     */
    public function reverseTransform($intervalId)
    {
        // no issue number? It's optional, so that's ok
        if (!$intervalId) {
            return;
        }

//        dd($intervalId);
        $interval = $this->em->getRepository(DeliveryDateInterval::class)
            // query for the issue with this id
            ->find($intervalId);


        if (null === $interval) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'HIBA: nem talált ilyen intervallumot: "%s"!',
                $intervalId
            ));
        }

//        dd($interval);

        return $interval;
    }
}