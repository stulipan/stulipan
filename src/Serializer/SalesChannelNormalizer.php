<?php

namespace App\Serializer;

use App\Entity\SalesChannel;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SalesChannelNormalizer  implements NormalizerInterface, NormalizerAwareInterface
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
            'shortcode'      => $object->getShortcode(),
            'enabled'       => $object->isEnabled(),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SalesChannel;
    }
}