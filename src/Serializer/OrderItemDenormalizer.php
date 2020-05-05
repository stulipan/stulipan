<?php

namespace App\Serializer;

use App\Entity\OrderItem;
use App\Entity\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * OrderItemDenormalizer denormalizer
 */
class OrderItemDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
        $order = $context['order'];
        
        if (isset($data['id'])) {
            $object = $this->em->find(OrderItem::class, $data['id']);
        } else {
            $object = new OrderItem();
            /** @var Product $product */
            $product = $this->em->find(Product::class, $data['product']['id']);
//            $product = $this->denormalizer->denormalize($data['product'],Product::class, $format, $context);
            $object->setOrder($order);
            $object->setProduct($product);
            $object->setQuantity(1);
            $object->setPrice($product->getPrice()->getNumericValue());
    
            $object->setPriceTotal($object->getPrice() * $object->getQuantity());
            $order->addItem($object);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != OrderItem::class) {
            return false;
        }
        return true;
    }
}