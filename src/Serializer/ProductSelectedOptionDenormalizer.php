<?php

namespace App\Serializer;

use App\Entity\Product\ProductSelectedOption;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * ProductSelectedOption denormalizer
 */
class ProductSelectedOptionDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
            /** @var ProductSelectedOption $object */
            $object = $this->em->find(ProductSelectedOption::class, $data['id']);
        } else {
            $object = new ProductSelectedOption();
        }

        if (isset($data['option'])) {
            try {
                $option = $product->findOptionBy(['name' => $data['option']['name']]);
//                dd($option);
            } catch(\Exception $e) {
                throw new Error( sprintf('HIBA: SelectedOption denormalize failed. $product->findOptionBy() could not find such a ProductOption. Possible cause: \'option.name\' field may be missing from the JSON file'));
            }
            $object->setOption($option);
        }
        if (isset($data['optionValue'])) {
            try {
                $optionValue = $option->findValueBy(['value' => $data['optionValue']['value']]);
            } catch(\Exception $e) {
                throw new Error( sprintf('HIBA: SelectedOption denormalize failed. $option->findValueBy() could not find such a ProductOptionValue. Possible cause: \'optionValue.value\' field may be missing from the JSON file'));
            }
            $object->setOptionValue($optionValue);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != ProductSelectedOption::class) {
            return false;
        }
        return true;
    }
}