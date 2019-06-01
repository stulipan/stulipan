<?php

namespace App\Serializer;

use App\Entity\Product\Product;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 */
class ProductNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'id'            => $object->getId(),
            'name'          => $object->getName(),
            'description'   => $object->getDescription(),
            'sku'           => $object->getSku(),
            'stock'         => $object->getStock(),
            'kind'          => $this->normalizer->normalize($object->getKind(), $format, $context),
            'status'        => $this->normalizer->normalize($object->getStatus(), $format, $context),
            'price'         => $this->normalizer->normalize($object->getPrice(), $format, $context),
            'categories'    => array_map(
                function ($object) use ($format, $context) {
                    return $this->normalizer->normalize($object, $format, $context);
                },
                $object->getCategories()->getValues()
                ),
            'attribute'     => $object->getAttributeName(),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Product;
    }
}