<?php

namespace App\Serializer;

use App\Entity\ImageEntity;
use App\Entity\Product\ProductImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * ProductImage denormalizer
 */
class ProductImageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
        $product = $context['product'];  // this is passed on from ProductDenormalizer
        
        if (isset($data['id'])) {
            $object = $this->em->find(ProductImage::class, $data['id']);
            $object->setOrdering($data['ordering']);
        } else {
            $object = new ProductImage();
            $imageEntity = $this->denormalizer->denormalize($data['image'],ImageEntity::class, $format, $context);
            $object->setImage($imageEntity);
            $object->setProduct($product);
            $object->setOrdering($data['ordering']);
            $context['product']->addImage($object);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductImage::class) {
            return false;
        }
        return true;
    }
}