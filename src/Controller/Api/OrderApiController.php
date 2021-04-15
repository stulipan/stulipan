<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Order;

use App\Services\OrderBuilder;
use App\Entity\OrderItem;
use App\Entity\Product\Product;
use App\Serializer\CustomerDenormalizer;
use App\Serializer\OrderAddressDenormalizer;
use App\Serializer\OrderDenormalizer;
use App\Serializer\OrderItemDenormalizer;
use App\Serializer\PaymentDenormalizer;
use App\Serializer\RecipientDenormalizer;
use App\Serializer\SenderDenormalizer;
use App\Serializer\ShippingDenormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/admin")
 */
class OrderApiController extends BaseController
{
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;
    
    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
        parent::__construct();
    }
    
    /**
     * Example: /admin/api/orders/2
     *
     * @Route("/api/orders/{id}", name="api-order-getOrder", methods={"GET"})
     */
    public function apiGetOrder(Request $request)
    {
        $id = $request->attributes->get('id');
        $data = $this->getDoctrine()->getRepository(Order::class)->find($id);
        if ($data) {
            return $this->jsonObjNormalized(['orders' => $this->toArray($data)], 200, ['groups' => 'orderView']);
        } else {
            $errors['message'] = sprintf('Nem talált ilyen rendelést: orderId=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     *
     * @Route("/api/orders/", name="api-order-createOrder", methods={"POST"})
     */
    public function apiCreateOrder(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $normalizer = [
            new OrderDenormalizer($em),
            new CustomerDenormalizer($em),
            new ArrayDenormalizer(),
            new OrderItemDenormalizer($em),
            new RecipientDenormalizer($em),
            new SenderDenormalizer($em),
            new OrderAddressDenormalizer($em),
            new ShippingDenormalizer($em),
            new PaymentDenormalizer($em),
        ];
        $serializer = new Serializer($normalizer, [new JsonEncoder()]);
        /** @var Order $order*/
        $order = $serializer->denormalize(json_decode($request->getContent(), true), Order::class,'json');
//        dd($order);

//        $errors = $this->getValidationErrors($order, $validator);
//        if (!empty($errors)) {
//            return $this->jsonNormalized(['errors' => $errors], 422);
//        }
        $em->persist($order);
        $em->flush();
        
        $this->orderBuilder->setCurrentOrder($order);
    
        return $this->jsonObjNormalized(['orders' => $this->toArray($order)], 200, ['groups' => 'orderView']);
    }
    
    /**
     * @Route("/api/orders/{id}", name="api-order-updateOrder", methods={"PUT"})
     */
    public function apiUpdateProduct(Request $request, Order $order)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }
    
        $em = $this->getDoctrine()->getManager();
        $normalizer = [
            new OrderDenormalizer($em),
            new CustomerDenormalizer($em),
            new ArrayDenormalizer(),
            new OrderItemDenormalizer($em),
            new RecipientDenormalizer($em),
            new SenderDenormalizer($em),
            new OrderAddressDenormalizer($em),
            new ShippingDenormalizer($em),
            new PaymentDenormalizer($em),
        ];
    
        $serializer = new Serializer($normalizer, [new JsonEncoder()]);
        $serializer->deserialize($request->getContent(),Order::class,'json', [
            'object_to_populate' => $order,
            'skip_null_values' => true,
            ]);
        $em->persist($order);
        $em->flush();
    
        return $this->jsonObjNormalized(['orders' => $this->toArray($order)], 200, ['groups' => 'orderView']);
    }
}