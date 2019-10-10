<?php

namespace App\Serializer;

use App\Entity\Product\ProductStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * ProductCategory normalizer
 */
class ProductStatusNormalizer  implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'id'        => $object->getId(),
            'name'      => $object->getName(),
            'icon'      => $object->getIcon()
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductStatus;
    }
}