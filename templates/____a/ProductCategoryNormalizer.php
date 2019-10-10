<?php

namespace App\Normalizer;

use App\Entity\Product\ParentCategory;
use App\Entity\Product\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;

class ProductCategoryNormalizer extends Denormalizer
{
    /**
    * @var EntityManagerInterface   NINCS HASZNALVA
    */
    protected $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductCategory::class) {
            return false;
        }
        if (!is_object($data)) {
            return false;
        }
        if (!isset($data->id)) {
            return false;
        }
        if (!isset($data->name)) {
            return false;
        }
        return true;
    
    }
    
    /**
     * @inheritDoc
     * @return ProductCategory
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $category = new ProductCategory();
        $category->setId($data->id);
        $category->setName($data->name);
        if (isset($data->parentCategory)) {
            /** @var ParentCategory $parentCat */
            $parentCat = $this->denormalizer->denormalize(
                $data->parentCategory,
                ProductCategory::class,
                $format,
                []
            );
            $category->setParentCategory($parentCat);
//            $category->setParentCategory($this->em->find(ProductCategory::class, $data->parentCategory->id));
        }
        return $category;
    }
}

//class ProductCategoryNormalizer extends ObjectNormalizer
//{
//    /**
//     * @var EntityManagerInterface
//     */
//    protected $em;
//
//    /**
//     * Entity normalizer
//     * @param EntityManagerInterface $em
//     * @param ClassMetadataFactoryInterface|null $classMetadataFactory
//     * @param NameConverterInterface|null $nameConverter
//     * @param PropertyAccessorInterface|null $propertyAccessor
//     * @param PropertyTypeExtractorInterface|null $propertyTypeExtractor
//     */
//    public function __construct(EntityManagerInterface $em, ?ClassMetadataFactoryInterface $classMetadataFactory = null,
//                                ?NameConverterInterface $nameConverter = null, ?PropertyAccessorInterface $propertyAccessor = null,
//                                ?PropertyTypeExtractorInterface $propertyTypeExtractor = null)
//    {
//        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
//        $this->em = $em;
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function supportsDenormalization($data, $type, $format = null)
//    {
////        dd($data);
//        if ($data['id'] == 2) {
//            dd($data);
////            dd(is_object($data) && $data instanceof ProductCategory);
//        }
//
////
//        return is_object($data) && $data instanceof ProductCategory;
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function denormalize($data, $class, $format = null, array $context = [])
//    {
//        dd($data->getName());
//        return $data->getName();
////        return $this->em->find($class, $data);
//    }
//}