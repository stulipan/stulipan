<?php

namespace App\Serializer;

use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\PaymentMethod;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\ShippingMethod;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Order denormalizer
 */
class OrderDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * {@inheritdoc}
     * @return Order
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id'])) {
            $object = $this->em->find(Order::class, $data['id']);
        } else {
            $object = new Order();
        }
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($data as $key => $value) {
            if (!is_array($value) && $key !== 'id' && $key !== 'deliveryDate') {
                $accessor->setValue($object, $key, $value);
            }
//            if ($key === 'deliveryDate') {
//                $accessor->setValue($object, $key, $value);
//            }
        }
        if (isset($data['items'])) {
            $initialItems = $object->getItems();
            $context = array_merge($context, ['order' => $object]);
            $items = $this->denormalizer->denormalize($data['items'],OrderItem::class.'[]', $format, $context);
            foreach ($initialItems as $item) {
                if (! (new ArrayCollection($items))->contains($item) ) {
                    $object->removeItem($item);
                }
            }
//            foreach ($object->getItems() as $item) {
//                $object->removeItem($item);
//            }
//            $items = $this->denormalizer->denormalize($data['items'],OrderItem::class.'[]', $format, $context);
//            foreach ($items as $item) {
//                $object->addItem($item);
//            }
        }
        if (isset($data['customer'])) {
            /** @var User $customer */
            $customer = $this->denormalizer->denormalize($data['customer'],User::class, $format, $context);
            $object->setCustomer($customer);
        }
        if (isset($data['recipient'])) {
            /** @var Recipient $recipient */
            $recipient = $this->denormalizer->denormalize($data['recipient'],Recipient::class, $format, $context);
            $object->setRecipient($recipient);
        }
        if (isset($data['sender'])) {
            /** @var Sender $sender */
            $sender = $this->denormalizer->denormalize($data['sender'],Sender::class, $format, $context);
            $object->setSender($sender);
        }
        if (isset($data['shippingAddress'])) {
            /** @var OrderAddress $shippingAddress */
            $shippingAddress = $this->denormalizer->denormalize($data['shippingAddress'],OrderAddress::class, $format, $context);
            $object->setShippingAddress($shippingAddress);
        }
        if (isset($data['billingAddress'])) {
            /** @var OrderAddress $billingAddress */
            $billingAddress = $this->denormalizer->denormalize($data['billingAddress'],OrderAddress::class, $format, $context);
            $object->setBillingAddress($billingAddress);
        }
        if (isset($data['shippingMethod'])) {
            /** @var ShippingMethod $shipping */
            $shipping = $this->denormalizer->denormalize($data['shippingMethod'],ShippingMethod::class, $format, $context);
            $object->setShippingMethod($shipping);
        }
        if (isset($data['paymentMethod'])) {
            /** @var PaymentMethod $payment */
            $payment = $this->denormalizer->denormalize($data['paymentMethod'],PaymentMethod::class, $format, $context);
            $object->setPaymentMethod($payment);
        }
        return $object;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != Order::class) {
            return false;
        }
        return true;
    }
}