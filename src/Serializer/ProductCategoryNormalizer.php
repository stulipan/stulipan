<?php

namespace App\Serializer;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;

/**
 * ProductCategory normalizer
 */
class ProductCategoryNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
//        $closure = function () {
//            $object = $this;
//            if ($object->getParent() instanceof ProductCategory) {
//                return [
//                    'id' => $object->getParent()->getId(),
//                    'name' => $object->getParent()->getName()
//                ];
//            }
//            return $object->getId();
//        };
//        dd($closure->call($object));
        return [
            'id'        => $object->getId(),
            'name'      => $object->getName(),
            'description'   => $object->getDescription(),
            'slug'       => $object->getSlug(),
            'enabled'     => $object->getEnabled(),
            'parent'          =>
//                $closure->call($object),
//                $this->normalizer->normalize($object->getParent())  // Ezzel az parent kategoria osszes mezeit kigeneralom/kinormalizalom
                function ($object) {
                    /** @var ProductCategory $object */
                    if ($object->getParent() instanceof ProductCategory) {
                        return [
                            'id' => $object->getParent()->getId(),
                            'name' => $object->getParent()->getName()
                        ];
                    }
                    // Ha az object-nek nincs parentje, akkor a jelenlegi object->getId-t adja vissza.
                    return $object->getId();
                },
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductCategory;
    }
}