<?php

namespace App\Serializer;

use App\Entity\Price;
use App\Entity\Product\Product;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductStatus;
use App\Entity\Product\ProductVariant;
use App\Entity\SalesChannel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

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
    
    /**
     * {@inheritdoc}
     * @return Product
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id'])) {
            $object = $this->em->find(Product::class, $data['id']);
        } else {
            $object = new Product();
        }
        $object->setName($data['name']);
        $object->setDescription($data['description']);
        $object->setSku($data['sku']);
        $object->setStock($data['stock']);

        $context = array_merge($context, ['product' => $object]);

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
        if (isset($data['options']) && count($data['options']) > 0) {
            $initialOptions = $object->getOptions();
            $options = $this->denormalizer->denormalize($data['options'],ProductOption::class.'[]', $format, $context);
            foreach ($options as $option) {
                $option->setProduct($object);
                $object->addOption($option);
            }
            foreach ($initialOptions as $item) {
                if (!(new ArrayCollection($options))->contains($item)) {
                    $object->removeOption($item);
                }
            }
        }
        if (isset($data['variants'])) {
            $initialVariants = $object->getVariants();
            $variants = $this->denormalizer->denormalize($data['variants'], ProductVariant::class.'[]', $format, $context);
            foreach ($variants as $variant) {
                $variant->setProduct($object);
                $object->addVariant($variant);
            }
            foreach ($initialVariants as $variant) {
                if (!(new ArrayCollection($variants))->contains($variant)) {
                    $object->removeVariant($variant);
                }
            }
        }
        if (isset($data['salesChannels'])) {
            foreach ($object->getSalesChannels() as $item) {
                $object->removeSalesChannel($item);
            }
            $salesChannels = $this->denormalizer->denormalize($data['salesChannels'],SalesChannel::class.'[]', $format, $context);
            foreach ($salesChannels as $item) {
                $object->addSalesChannel($item);
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