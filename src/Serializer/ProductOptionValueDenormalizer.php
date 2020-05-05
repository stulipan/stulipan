<?php

namespace App\Serializer;

use App\Entity\Product\ProductOptionValue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * ProductOptionValue denormalizer
 */
class ProductOptionValueDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
        if (isset($data['id'])) {
            /** @var ProductOptionValue $object */
            $object = $this->em->find(ProductOptionValue::class, $data['id']);

            if (isset($data['value'])) {
                // Verify if previous value equals new data which was received from the PUT query
                if ($object->getValue() != $data['value']) {

                    // Verify if new value is the same with any other OptionValues for this ProductOption
                    foreach ($object->getOption()->getValues() as $optionValue) {
                        if ($data['value'] == $optionValue->getValue()) {
                            return $optionValue;
                        }
                    }

                    $newOptionValue = new ProductOptionValue();
                    $newOptionValue->setValue($data['value']);
                    $newOptionValue->setPosition($object->getGreatestPosition() + 1);
                    $newOptionValue->setOption($object->getOption());

                    $object->getOption()->addValue($newOptionValue);
                    return $newOptionValue;
                }
            }
        } else {
            if (isset($data['value'])) {
                    $newOptionValue = new ProductOptionValue();
                    $newOptionValue->setValue($data['value']);
//                    $newOptionValue->setPosition($object->getGreatestPosition() + 1);
//                    $newOptionValue->setOption($object->getOption());

//                    $object->getOption()->addValue($newOptionValue);
                    return $newOptionValue;
            }
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductOptionValue::class) {
            return false;
        }
        return true;
    }
}