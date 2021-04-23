<?php

namespace App\Serializer;

use App\Entity\Price;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Price denormalizer
 */
class PriceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id']) && $data['id'] !== null) {
            $object = $this->em->find(Price::class, $data['id']);
            if (isset($data['numericValue'])) {
                $object->setNumericValue($data['numericValue']);
            }
        } else {
            $object = new Price();
            $object->setNumericValue($data['numericValue']);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != Price::class) {
            return false;
        }
        return true;
    }
}