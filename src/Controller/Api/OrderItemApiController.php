<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Order;

use App\Services\OrderBuilder;
use App\Entity\OrderItem;
use App\Entity\Product\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class OrderItemApiController extends BaseController
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
     * Example: /admin/api/orders/2/items
     *
     * @Route("/api/orders/{id}/items/", name="api-order-getOrderItems", methods={"GET"})
     */
    public function apiGetOrderItems(Request $request)
    {
        $id = $request->attributes->get('id');
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);
        $data = $this->getDoctrine()->getRepository(OrderItem::class)->findBy(['order' => $order]);
        if ($data) {
            return $this->jsonObjNormalized(['items' => $this->toArray($data)], 200, ['groups' => 'orderView']);
        } else {
            $errors['message'] = sprintf('Nem talált ilyen rendelést: orderId=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     *
     * @Route("/api/orders/{id}/items/{productId}", name="api-order-AddItem", methods={"PUT"})
     *
     * @ParamConverter("product", options={"id" = "productId"})
     */
    public function apiAddItem(Request $request, Order $order, Product $product)
    {
        if (!$order->containsTheProduct($product)) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(1);
            $orderItem->setUnitPrice($product->getPrice()->getNumericValue());
        
            $orderItem->setPriceTotal($orderItem->getUnitPrice() * $orderItem->getQuantity());
            $order->addItem($orderItem);
        } else {
            $key = $order->indexOfProduct($product);
            /** @var OrderItem $item */
            $item = $order->getItems()->get($key);
            
            // Is there enough of this product on stock?
            // If current quantity + 1 is less than or equal to current stock it will add it to the cart
            if ($item->getQuantity()+1 <= $product->getStock()) {
                $item->setQuantity($item->getQuantity()+1);
                $price = $product->getPrice()->getNumericValue();
                $item->setUnitPrice($price);
                $item->setPriceTotal($item->getUnitPrice() * $item->getQuantity());
            } else {
                $errors['message'] = sprintf('A termékből nincs több készleten');
            }
        }
        
        if (isset($errors)) {
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    
        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();
        
        $data = $order->getItems()->getValues();
        if ($data) {
            return $this->jsonObjNormalized(['items' => $this->toArray($data)], 200, ['groups' => 'orderView']);
        } else {
            $errors['message'] = sprintf('Nem talált tételeket, ugyanis nem talált ilyen rendelést: orderId=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     *
     * @Route("/api/orders/{id}/items/{itemId}", name="api-order-removeItem", methods={"DELETE"})
     *
     * @ParamConverter("orderItem", options={"id" = "itemId"})
     */
    public function apiRemoveItem(Request $request, Order $order, OrderItem $orderItem)
    {
        if ($order && $order->getItems()->contains($orderItem)) {
            $order->removeItem($orderItem);
            $this->getDoctrine()->getManager()->persist($order);
            $this->getDoctrine()->getManager()->flush();
        }
        $data = $order->getItems()->getValues();
        return $this->jsonObjNormalized(['items' => $this->toArray($data)], 200, ['groups' => 'orderView']);
//        if ($data) {
//            return $this->jsonObjNormalized(['items' => $this->toArray($data)], 200, ['groups' => 'orderView']);
//        } else {
//            $errors['message'] = sprintf('A kosarad üres');
//            return $this->jsonNormalized(['errors' => [$errors]], 422);
//        }
    }
    
    /**
     * @Route("/api/items/{id}", name="api-order-updateItemQuantity", methods={"PUT"})
     *
     */
    public function apiUpdateItemQuantity(Request $request, OrderItem $orderItem)
    {
        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'];
        if ($quantity <= $orderItem->getProduct()->getStock()) {
            $orderItem->setQuantity($quantity);
            $price = $orderItem->getProduct()->getPrice()->getNumericValue();
            $orderItem->setPriceTotal($price * $quantity);
        } else {
            $errors['message'] = sprintf('A termékből csupán %s db van készleten.', $orderItem->getProduct()->getStock());
        }
    
        if (isset($errors)) {
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    
        $this->getDoctrine()->getManager()->persist($orderItem);
        $this->getDoctrine()->getManager()->flush();
    
        $data = $orderItem;
        if ($data) {
            return $this->jsonObjNormalized(['items' => $this->toArray($data)], 200, ['groups' => 'orderView']);
        }
    }
}