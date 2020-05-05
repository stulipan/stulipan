<?php

namespace App\Serializer;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * ProductCategory denormalizer
 */
class ProductOptionDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
            $object = $this->em->find(ProductOption::class, $data['id']);
        } else {
            $object = new ProductOption();
            $object->setName($data['name']);
            $object->setPosition($data['position']);
            $object->setProduct($product);
        }

        if (isset($data['values'])) {
            $initialValues = $object->getValues();
            $values = $this->denormalizer->denormalize($data['values'], ProductOptionValue::class.'[]', $format, $context);
            foreach ($values as $key => $value) {
                $value->setOption($object);
                $value->setPosition($key + 1);
                $object->addValue($value);
            }
            foreach ($initialValues as $item) {
                if (!(new ArrayCollection($values))->contains($item)) {
                    $object->removeValue($item);
                }
            }
        } else {
            foreach ($object->getValues() as $item) {
                $object->removeValue($item);
            }
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductOption::class) {
            return false;
        }
        return true;
    }
}