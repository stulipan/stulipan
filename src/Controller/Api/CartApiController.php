<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Model\MessageAndCustomer;
use App\Entity\Order;

use App\Services\OrderBuilder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/admin")
 */
class CartApiController extends BaseController
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
     * @Route("/api/orders/{id}/message-customer", name="api-order-addMessageAndCustomer", methods={"POST"})
     */
    public function apiAddMessageAndCustomer(Request $request, Order $order, ValidatorInterface $validator)
    {
        /** @var MessageAndCustomer $messageAndCustomer */
        $messageAndCustomer = $this->serializer->denormalize(json_decode($request->getContent(), true),MessageAndCustomer::class,'json',[]);
        $errors = $this->getValidationErrors($messageAndCustomer, $validator);
        if (!empty($errors)) {
            return $this->jsonNormalized(['errors' => $errors], 422);
        }
        
//        // Use this to validate incoming data using Form
//        $data = json_decode($request->getContent(), true);
//        $form = $this->createForm(MessageAndCustomerFormType::class, null, ['csrf_protection' => false,]);
//        $form->submit($data);
//
//        if (!$form->isValid()) {
//            $errors = $this->getErrorsFromForm($form);
//            return $this->jsonNormalized(['errors' => $errors], 422);
//        }
//        /** @var MessageAndCustomer $data */
//        $data = $form->getData();
//        $card = $data->getCard();
//        $customerBasic = $data->getCustomer();
        
        if ($messageAndCustomer->getCard()) {
            $order->setMessage($messageAndCustomer->getCard()->getMessage());
            $order->setMessageAuthor($messageAndCustomer->getCard()->getAuthor());
        }
        
        if ($messageAndCustomer->getCustomer()) {
            $order->setEmail($messageAndCustomer->getCustomer()->getEmail());
            $order->setBillingName($messageAndCustomer->getCustomer()->getFullname());
            $order->setBillingPhone($messageAndCustomer->getCustomer()->getPhone());
        }
    
        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();
       
        //  !!!!!!!!!!!!!!!!!!!!
        //  Kelleni fog hogy az API is session alapu legyen, ugyanis az email, firstname, lastname session-be kell elmenteni !!!!
//        $order->setCustomerBasic($data->getCustomer());
    
        return $this->createJsonResponse(['messageAndCustomer' => [$messageAndCustomer]],200, ['groups' => 'orderView']);
       
    }

}