<?php

namespace App\Serializer;

use App\Entity\Price;
use App\Entity\Product\ProductSelectedOption;
use App\Entity\Product\ProductVariant;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * ProductCategory denormalizer
 */
class ProductVariantDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
        $product = $context['product'];  // this is passed on from ProductDenormalizer
        if (isset($data['id'])) {
            $object = $this->em->find(ProductVariant::class, $data['id']);
        } else {
            $object = new ProductVariant();
            $object->setProduct($product);
        }

        if (isset($data['selectedOptions'])) {
            $selectedOptions = $this->denormalizer->denormalize($data['selectedOptions'], ProductSelectedOption::class.'[]', $format, $context);
            foreach ($selectedOptions as $option) {
                $option->setVariant($object);
                $object->addSelectedOption($option);
            }
        }

        if (isset($data['name'])) {
            $object->setName($data['name']);
        }
        if (isset($data['price'])) {
            $price = $this->denormalizer->denormalize($data['price'], Price::class, $format, $context);
            $object->setPrice($price);
        }
        if (isset($data['position'])) {
            $object->setPosition($data['position']);
        }
        if (isset($data['sku'])) {
            $object->setSku($data['sku']);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductVariant::class) {
            return false;
        }
        return true;
    }
}