<?php

namespace App\Serializer;

use App\Entity\Product\ProductCategory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * ProductCategory normalizer
 */
class ProductCategoryNormalizerObj extends ObjectNormalizer //implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    
    protected $defaultContext;
    
    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null, ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, callable $objectClassResolver = null, array $defaultContext = [])
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->defaultContext = [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
//                ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER=>function ($object) {
//                    /** @var ProductCategory $object */
//                    if ($object instanceof ProductCategory) {
//                        return ['id' => $object->getId(), 'name' => $object->getName()];
//                    }
//                    return $object->getId();
//                }
            ];
//        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
//        dd($this->defaultContext);
        parent::__construct($classMetadataFactory, null, null, null, null, null, $this->defaultContext);
    }
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $closure = function () {
            $object = $this;
            /** @var ProductCategory $object */
            if ($object->getParent() instanceof ProductCategory) {
                return [
                    'id' => $object->getParent()->getId(),
                    'name' => $object->getParent()->getName()
                ];
            }
            return null;
        };
//        $this->isAllowedAttribute($object, $attributeName, $format, $context)
        return [
            'id'            => $object->getId(),
            'name'          => $object->getName(),
            'description'   => $object->getDescription(),
            'slug'          => $object->getSlug(),
            'enabled'       => $object->getEnabled(),
            'parent'        => $closure->call($object),
        
//                function ($object) {
//                    /** @var ProductCategory $object */
//                    if ($object->getParent()) {
//                        return [
//                            'id' => $object->getParent()->getId(),
//                            'name' => $object->getParent()->getName()
//                        ];
//                    }
//                    // Ha az object-nek nincs parentje, akkor a jelenlegi object->getId-t adja vissza.
//                    return $object->getName();
//                },
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