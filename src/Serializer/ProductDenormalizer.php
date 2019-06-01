<?php

namespace App\Serializer;

use App\Entity\ImageEntity;
use App\Entity\Price;
use App\Entity\Product\Product;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductStatus;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Product denormalizer
 */
class ProductDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
//    private $normalizer;
//
//    public function __construct() //ObjectNormalizer $normalizer
//    {
//        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//        $objNormalizer = new ObjectNormalizer(
//            $classMetadataFactory,
//            null,
//            null,
////            new ReflectionExtractor(),
//            new PhpDocExtractor(),
//            null,
//            null,
//            [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
//        );
//        $normalizer = [
//            $objNormalizer,
//        ];
//        $serializer = new Serializer($normalizer, [new JsonEncoder()]);
//        $this->normalizer = $serializer;
//    }
    
    /**
     * {@inheritdoc}
     * @return Product
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
//        $object = $this->normalizer->denormalize($data, $class, $format, $context);
//        $clasa = json_decode(json_encode($data));
//        dd($object);
        
        if (isset($data['id'])) {
            $object = $this->em->find(Product::class, $data['id']);
        } else {
            $object = new Product();
        }
        $object->setName($data['name']);
        $object->setDescription($data['description']);
        $object->setSku($data['sku']);
        $object->setStock($data['stock']);
        $object->setName($data['name']);
        if (isset($data['kind'])) {
            $kind = $this->denormalizer->denormalize($data['kind'],ProductKind::class, $format, $context);
            $object->setKind($kind);
        }
        if (isset($data['status'])) {
            $status = $this->denormalizer->denormalize($data['status'],ProductStatus::class, $format, $context);
            $object->setStatus($status);
        }
        if (isset($data['price'])) {
            $price = $this->denormalizer->denormalize($data['price'],Price::class, $format, $context);
            $object->setPrice($price);
        }
        if (isset($data['categories'])) {
//            dd($object->getCategories()->getValues());
            foreach ($object->getCategories() as $category) {
                $object->removeCategory($category);
            }
            $categories = $this->denormalizer->denormalize($data['categories'],ProductCategory::class.'[]', $format, $context);
            foreach ($categories as $category) {
                $object->addCategory($category);
            }
        }
        if (isset($data['badges'])) {
            foreach ($object->getBadges() as $badge) {
                $object->removeBadge($badge);
            }
            $badges = $this->denormalizer->denormalize($data['badges'],ProductBadge::class.'[]', $format, $context);
            foreach ($badges as $badge) {
                $object->addBadge($badge);
            }
        }
        if (isset($data['images'])) {
            $initialImages = $object->getImages();
            
            $context = array_merge($context, ['product' => $object]);
            $images = $this->denormalizer->denormalize($data['images'],ProductImage::class.'[]', $format, $context);
            // normalisan ide kene egy: $object->addImage() foreach loop-ban
            // de nem kell, mivel az uj kepek a ProductImageDenormalizerben vannak hozzaadva!
            
            // delete ProductImages that are not common
            foreach ($initialImages as $image) {
                if (! (new ArrayCollection($images))->contains($image) ) {
                    $object->removeImage($image);
                }
            }
        }
        return $object;
    }
    
    public function getElementCommonInXXAndZZ(ArrayCollection $xx, ArrayCollection $zz)
    {
        return $elementsCommon = $xx->filter(function ($element) use ($zz) {
            return $zz->contains($element) === true;
        });
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != Product::class) {
            return false;
        }
        return true;
    }
}