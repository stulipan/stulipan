<?php

namespace App\Controller\Shop;

use App\Entity\Order;
use App\Entity\OrderBuilder;
use App\Entity\Sender;
use App\Form\SenderType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartSenderController extends AbstractController
{
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;

    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * Handles the Sender form. It is used to create and submit the form from JS.
     *
     * @Route("/cart/editSender/{id}", name="cart-editSender")
     */
    public function editSenderForm(Request $request, ?Sender $sender, $id = null)
    {
        $orderBuilder = $this->orderBuilder;
        $customer = $orderBuilder->getCurrentOrder()->getCustomer() ? $orderBuilder->getCurrentOrder()->getCustomer() : null;
        if (!$sender) {
            $sender = new Sender();
            $sender->setCustomer($customer);
            $form = $this->createForm(SenderType::class, $sender);
        } else {
            $form = $this->createForm(SenderType::class, $sender);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //elobb elmentem a sender formadatokat a Sender tablaba
            $sender = $form->getData();
            if ($customer) {
                $sender->setCustomer($customer); // a feladót egy Customerhez kotjuk
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sender);
            $entityManager->flush();

            $orderBuilder->setSender($sender);

            /**
             * If AJAX request, returns the list of Recipients
             */
            if ($request->isXmlHttpRequest()) {
                return $this->redirectToRoute('cart-getSenders');
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/sender_form.html.twig', [
                'order' => $orderBuilder,
                'senderForm' => $form->createView(),
            ]);
            return new Response($html,400);

        }
//        if ($request->isXmlHttpRequest()) {
//            return $this->render('webshop/cart/recipient_form.html.twig', [
//                'order' => $orderBuilder,
//                'recipientForm' => $form->createView(),
//            ]);
//        }
        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/sender_form.html.twig', [
            'order' => $orderBuilder,
            'senderForm' => $form->createView(),
        ]);
    }

    /**
     * Gets the list of Senders. Handles 2 situations, see getRecipients()
     *
     * @Route("/cart/getSenders", name="cart-getSenders")
     */
    public function getSenders()
    {
        $orderBuilder = $this->orderBuilder;
        /**
         * If the Order has a Customer, returns the list of the customer's Senders
         */
        if ($orderBuilder->getCurrentOrder()->getCustomer()) {
            $senders = $orderBuilder->getCurrentOrder()->getCustomer()->getSenders();
        }
        /**
         * Else, simply returns the Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $senders = new ArrayCollection();
            $senders->add($orderBuilder->getCurrentOrder()->getSender());
//            dd($senders);
        }

        return $this->render('webshop/cart/sender_list.html.twig', [
            'senders' => $senders,
            'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
        ]);
    }

    /**
     * Picks a Sender from the sender list and assigns it to the current Order.
     * It is used in JS.
     *
     * @Route("/cart/pickSender/{id}", name="cart-pickSender")
     */
    public function pickSender(Request $request, Sender $sender)
    {
        $orderBuilder = $this->orderBuilder;
        $orderBuilder->setSender($sender);
        $html = $this->render('admin/item.html.twig', [
            'item' => 'Számlázási címzett sikeresen kiválasztva!',
        ]);
        return new Response($html, 200);
    }

    /**
     * NO LONGER USED!!!!
     * This came with the original script!!!
     *
     * Creates an empty form with Sender fields
     */
    public function createSenderForm(Order $order): Response
    {
        $form = $this->createForm(SenderType::class, $order->getSender());

        //megmondom a formnak a Customer-t (az ID-jat)
        $form->get('customer')->setData($order->getCustomer()->getId());

        return $this->render('webshop/cart/sender_form.html.twig', [
            'senderForm' => $form->createView()
        ]);
    }

    /**
     * NO LONGER USED!!!!
     * Saves the data entered in Sender fields
     *
     * param int $id
     *
     * @Route("/cart/setSenderr", name="cart_set_sender", methods={"POST"})
     *
     */
    public function setSender(Request $request, Order $order): Response
    {
//        $order = $this->orderBuilder;
        $form = $this->createForm(SenderType::class, $order->getSender());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //elobb elmentem a sender formadatokat a Sender tablaba
            $sender = $form->getData();
            $sender->setCustomer($order->getCustomer());  // a sendert egy Customerhez kotjuk
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sender);

            $order->setSender($sender);
            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'A Sender sikeresen elmentve.');
        }

        return $this->redirectToRoute('site-checkout');
    }
}