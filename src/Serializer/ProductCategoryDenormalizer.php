<?php

namespace App\Serializer;

use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductKind;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * ProductCategory denormalizer
 */
class ProductCategoryDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (isset($data['id'])) {
            $object = $this->em->find(ProductCategory::class, $data['id']);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductCategory::class) {
            return false;
        }
        return true;
    }
}