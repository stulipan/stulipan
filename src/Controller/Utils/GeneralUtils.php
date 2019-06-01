<?php

namespace App\Controller\Utils;

use App\Entity\VatRate;
use App\Entity\VatValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GeneralUtils //extends AbstractController
{
    /**
     * The value means hours. A customer can pick delivery date only after:
     * \DateTime('now')+DELIVERY_DATE_HOUR_OFFSET
     */
    public const DELIVERY_DATE_HOUR_OFFSET = 4; // ha 1, akkor ez a default dátum típus;

    public const ORDER_NUMBER_FIRST_DIGIT = 2; // minden rendeles ilyen lesz: 5xxx-xxxxx
    public const ORDER_NUMBER_RANGE = 10000;


    //
    public function formatPhoneNumber(?string $phoneNumber, string $regionCode = '')
    {
        /**
         * If regionCode is missing, set default value to 'HU'
         */
        if (null === $regionCode || '' === $regionCode) {
            $regionCode = 'HU';
        }
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneProto = $phoneUtil->parse($phoneNumber, $regionCode);
            return (int)$phoneProto->getCountryCode().$phoneProto->getNationalNumber();

            } catch (\libphonenumber\NumberParseException $e) {
                return;
            }
    }
}