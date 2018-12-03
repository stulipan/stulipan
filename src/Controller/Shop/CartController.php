<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Order;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\OrderItem;
use App\Entity\Recipient;
use App\Entity\Shipping;
use App\Entity\Payment;

use App\Form\AjaxRecipientType;
use App\Form\CartAddItemType;
use App\Form\CheckoutFormType;
use App\Form\ClearCartType;
use App\Form\PaymentType;
use App\Form\RecipientType;
use App\Form\SenderType;
use App\Form\MessageType;
use App\Form\RemoveItemType;
use App\Form\ItemQuantityType;
use App\Form\SetItemQuantityType;

use App\Form\CartStepOneType;
use App\Form\ShippingType;
use App\Form\SetDiscountType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CartController extends Controller
{
    /**
     * @Route("/kosar", name="site-cart")
     */
    public function showCartPage()
    {
        // Ezt be kell szúrni a service.yaml-ba
        // App\Entity\OrderBuilder:
        //    public: true
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $clearForm = $this->createForm(ClearCartType::class, $orderBuilder->getCurrentOrder());

        $setDiscountForm = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());

        //ez CSAK akkor kell ha nem renderelem bele a template-be!!
//        $messageForm = $this->createForm(MessageType::class, $orderBuilder->getCurrentOrder());

        $recipients = $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients();

        if ($recipients->isEmpty()) {
            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $recipientForm = $this->createForm(AjaxRecipientType::class, $recipient);

            return $this->render('webshop/site/checkout_cart.html.twig', [
                'order' => $orderBuilder,
                'clearForm' => $clearForm->createView(),
                'setDiscountForm' => $setDiscountForm->createView(),
                'itemsInCart' => $orderBuilder->countItems(),
                'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
//                'messageForm' => $messageForm->createView(),
                'recipientForm' => $recipientForm->createView(),
            ]);
        }

        return $this->render('webshop/site/checkout_cart.html.twig', [
            'order' => $orderBuilder,
            'clearForm' => $clearForm->createView(),
            'setDiscountForm' => $setDiscountForm->createView(),
            'itemsInCart' => $orderBuilder->countItems(),
            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
//            'messageForm' => $messageForm->createView(),
            'recipients' => $recipients,
            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient()->getId(),
        ]);
    }

    /**
     * @Route("/penztar", name="site-checkout")
     */
    public function showCheckoutPage()
    {
        /**
         * Ezt be kell szúrni a service.yaml-ba
         * App\Entity\OrderBuilder:
         *    public: true
         */
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $checkoutForm = $this->createForm(CheckoutFormType::class, $orderBuilder->getCurrentOrder()); //,
//        dump($checkoutForm);die;
//        $shippingForm = $this->createForm(ShippingType::class, $orderBuilder->getCurrentOrder());
//        $paymentForm = $this->createForm(PaymentType::class, $orderBuilder->getCurrentOrder());

        $shippingMethods = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAllOrdered();
        $paymentMethods = $this->getDoctrine()
            ->getRepository(Payment::class)
            ->findAllOrdered();

        $senderForm = $this->createForm(SenderType::class, $orderBuilder->getCurrentOrder()->getSender());

        return $this->render('webshop/site/checkout_checkout.html.twig', [
            'order' => $orderBuilder,
            'form' => $checkoutForm->createView(),
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $paymentMethods,
//            'shippingForm' => $shippingForm->createView(),
//            'paymentForm' => $paymentForm->createView(),
            'senderForm' => $senderForm->createView(),
        ]);
    }

    /**
     * @Route("/cart/setCheckout", name="cart-setCheckout", methods={"POST", "GET"})
     */
    public function setCheckout(Request $request): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(CheckoutFormType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->getData()->getShipping()) {
                $orderBuilder->setShipping($form->getData()->getShipping());
            }
            if ($form->getData()->getPayment()) {
                $orderBuilder->setPayment($form->getData()->getPayment());
            }
            if ($form->isValid()) {
                return $this->redirectToRoute('site-checkout');
            }
        }

        $shippingMethods = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAllOrdered();
        $paymentMethods = $this->getDoctrine()
            ->getRepository(Payment::class)
            ->findAllOrdered();

        $senderForm = $this->createForm(SenderType::class, $orderBuilder->getCurrentOrder()->getSender());

        return $this->render('webshop/site/checkout_checkout.html.twig', [
            'order' => $orderBuilder,
            'form' => $form->createView(),
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $paymentMethods,
            'senderForm' => $senderForm->createView(),
        ]);
    }

    /**
     * @Route("/cart/setShipping", name="cart-setShipping", methods={"POST"})
     */
    public function setShipping(Request $request): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(ShippingType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setShipping($form->getData()->getShipping());
            $this->addFlash('success', 'A szállítási mód sikeresen beállítva.');
        }
        return $this->redirectToRoute('site-cart');
    }

    /**
     * @Route("/cart/setPayment", name="cart-setPayment", methods={"POST"})
     */
    public function setPayment(Request $request): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(PaymentType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setPayment($form->getData()->getPayment());
            $this->addFlash('success', 'A fizetési mód sikeresen beállítva.');
        }
        return $this->redirectToRoute('site-cart');
    }


    /**
     * @Route("/cart/getRecipients", name="cart-getRecipients")
     */
    public function getRecipients()
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $recipients = $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients();
        return $this->render('webshop/cart/recipient_list.html.twig', [
            'recipients' => $recipients,
            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient()->getId(),
        ]);
    }

    /**
     * @Route("/cart/removeItemFromCart/{id}", name="cart-removeItem")
     */
    public function removeItemFromCart(Request $request, OrderItem $item): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $orderBuilder->removeItem($item);
//        $html = $this->renderView('webshop/cart/product_list.html.twig', [
//            'order' => $orderBuilder->getCurrentOrder(),
//        ]);
//        return new Response($html, 200);
        if ($request->isXmlHttpRequest()) {
            return $this->render('webshop/cart/product_list.html.twig', [
                'order' => $orderBuilder,
            ]);
        }
        return $this->render('webshop/cart/product_list.html.twig', [
            'order' => $orderBuilder,
        ]);
    }

    public function setItemQuantityForm(OrderItem $item): Response
    {
        $form = $this->createForm(SetItemQuantityType::class, $item);
        return $this->render('webshop/site/_setItemQuantity_form.html.twig', [
            'quantityForm' => $form->createView()
        ]);
    }
    /**
     * @Route("/cart/setItemQuantity/{id}", name="cart-setItemQuantity", methods={"POST"})
     */
    public function setQuantity(Request $request, OrderItem $item): Response
    {
        $form = $this->createForm(SetItemQuantityType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setItemQuantity($item, $form->getData()->getQuantity());
            /**
             * If AJAX request, renders and returns an HTML form with the value
             */
            if ($request->isXmlHttpRequest()) {

                return $this->render('webshop/site/_setItemQuantity_form.html.twig', [
                    'quantityForm' => $form->createView(),
                ]);
            }
        }

        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->render('webshop/site/_setItemQuantity_form.html.twig', [
                'quantityForm' => $form->createView()
            ]);
            return new Response($html,400);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/site/_setItemQuantity_form.html.twig', [
                'quantityForm' => $form->createView(),
            ]);
    }

    /**
     * @Route("/cart/pickRecipient/{id}", name="cart-pickRecipient")
     */
    public function pickRecipient(Request $request, Recipient $recipient)
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $orderBuilder->setRecipient($recipient);
        $html = $this->render('admin/item.html.twig', [
            'item' => 'Címzett sikeresen kiválasztva!',
        ]);
        return new Response($html, 200);
    }

    /**
     * @Route("/cart/editRecipient/{id}", name="cart-editRecipient")
     */
    public function editRecipientForm(Request $request, ?Recipient $recipient, $id = null)
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        if (!$recipient) {
            $recipientt = new Recipient();
            $recipientt->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $form = $this->createForm(AjaxRecipientType::class, $recipientt);
        } else {
            $form = $this->createForm(AjaxRecipientType::class, $recipient);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //elobb elmentem a recipient formadatokat a Recipient tablaba
            $recipient = $form->getData();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());  // a cimzettet egy Customerhez kotjuk
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipient);
            $entityManager->flush();

//            $orderBuilder->setRecipient($recipient);

            /**
             * If AJAX request, renders and returns an HTML form with the value
             */
            if ($request->isXmlHttpRequest()) {

//                return $this->render('webshop/cart/recipient_list.html.twig', [
//                    'recipients' => $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients(),
//                ]);
                return $this->redirectToRoute('cart-getRecipients');
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/recipient_form.html.twig', [
                'order' => $orderBuilder,
                'recipientForm' => $form->createView(),
            ]);
            return new Response($html,400);

        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('webshop/cart/recipient_form.html.twig', [
                'order' => $orderBuilder,
                'recipientForm' => $form->createView(),
            ]);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/site/__ajax-form.html.twig', [
            'order' => $orderBuilder,
            'recipientForm' => $form->createView(),
        ]);
    }





    /**
     * Renders the Pick A Gift module in the cart page.
     */
    public function pickAGift()
    {
        $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();

        return $this->render('webshop/cart/gift_widget.html.twig', ['termekek' => $products]);
    }


    public function addItemForm(Product $product): Response
    {
        $form = $this->createForm(CartAddItemType::class, $product);

        return $this->render('webshop/site/_addItem_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function removeItemForm(OrderItem $item): Response
    {
        $form = $this->createForm(RemoveItemType::class, $item);

        return $this->render('webshop/site/_removeItem_form.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * param int $id
     *
     * @Route("/cart/addItem/{id}", name="cart_add_item", methods={"POST"})
     *
     */
    public function addItem(Request $request, Product $product): Response
    {
        $form = $this->createForm(CartAddItemType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // kreálok egy OrderBuilder-t, amibe rakok egy terméket. A mennyiséget a formból szedem ki.
            $this->get('App\Entity\OrderBuilder')->addItem($product, $form->get('quantity')->getData());

            $this->addFlash('success', 'A termék sikeresen a kosárba került.');
        }

        return $this->redirectToRoute('site-cart');
    }

//    /**
//     * @Route("/cart/removeItem/{id}", name="cart_remove_item", methods={"POST"})
//     */
//    public function removeItem(Request $request, OrderItem $item): Response
//    {
//        $form = $this->createForm(RemoveItemType::class, $item);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $this->get('App\Entity\OrderBuilder')->removeItem($item);
//            $this->addFlash('success', 'A termék sikeresen törölve.');
//        }
//        return $this->redirectToRoute('site-cart');
//    }



//    /**
//     * Creates an empty form with Recipient fields
//     * To be used in template with render(controller()) when you know the Order (id)
//     */
//    public function createRecipientForm(Order $order): Response
//    {
//        $form = $this->createForm(RecipientType::class, $order->getRecipient());
//
//        return $this->render('webshop/cart/recipient_form.html.twig', [
//            'form' => $form->createView()
//        ]);
//    }
//
//    /**
//     * Saves the data entered in Recipient fields
//     *
//     * param int $id
//     *
//     * @Route("/cart/setRecipient", name="cart_set_recipient", methods={"POST"})
//     *
//     */
//    public function setRecipient(Request $request): Response  //, Order $order
//    {
//        $orderBuilder = $this->get('App\Entity\OrderBuilder');
////        dump($orderBuilder);die;
//        $form = $this->createForm(RecipientType::class);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            //elobb elmentem a recipient formadatokat a Recipient tablaba
//            $recipient = $form->getData();
////            dump($orderBuilder->getCustomer());die;
//            $recipient->setCustomer($orderBuilder->getCustomer());  // a cimzettet egy Customerhez kotjuk
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($recipient);
//            $entityManager->flush();
//
//            $orderBuilder->setRecipient($recipient);
//
//            //hozzaadtom a current orderhez, az ujonnan rogzitett recipientet ($cimzett)
////            $orderBuilder->getCurrentOrder()->setRecipient($recipient);
//
//            $this->addFlash('success', 'A Recipient sikeresen elmentve.');
//        }
//
//        return $this->redirectToRoute('site-cart');
//    }

    public function createMessageForm(): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(MessageType::class, $orderBuilder->getCurrentOrder());

        return $this->render('webshop/cart/message_form.html.twig', [
            'messageForm' => $form->createView(),
            'order' => $orderBuilder->getId()
        ]);
    }

    /**
     * Saves the data entered in Message fields
     *
     * param int $id
     *
     * @Route("/cart/setMessage", name="cart-setMessage", methods={"POST"})
     *
     */
    public function setMessage(Request $request): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(MessageType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Ha mindkét mező ki van töltve, akkor elmentem, amúgy meg
             * megnézem melyik mező maradt üressen és hibát rendelek hozzá.
             */
            if ( (!$form->get('message')->isEmpty() && !$form->get('messageAuthor')->isEmpty()) || ($form->get('message')->isEmpty() && $form->get('messageAuthor')->isEmpty()) ) {
                $this->get('App\Entity\OrderBuilder')->setMessageAndAuthor($form->getData()->getMessage(),$form->getData()->getMessageAuthor());
                /**
                 * If AJAX request, renders and returns an HTML form with the value
                 */
                if ($request->isXmlHttpRequest()) {
                    return $this->redirectToRoute('site-checkout');
                }
            } else {
                if ($form->get('message')->isEmpty()) {
                    $form->get('message')->addError(new FormError('Ajaj, az üzenet lemaradt!'));
                } else {
                    $form->get('messageAuthor')->addError(new FormError('Írd alá az uzenetet!'));
                }
            }
        }

        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/message_form.html.twig', [
                'messageForm' => $form->createView(),
                'order' => $orderBuilder->getId()
            ]);
            return new Response($html,400);

        }

//        if ($request->isXmlHttpRequest()) {
//            return $this->render('webshop/cart/message_form.html.twig', [
//                'messageForm' => $form->createView(),
//                'order' => $orderBuilder->getId()
//            ]);
//        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/message_form.html.twig', [
            'messageForm' => $form->createView(),
            'order' => $orderBuilder->getId()
        ]);
    }

    /**
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
     * Saves the data entered in Sender fields
     *
     * param int $id
     *
     * @Route("/cart/setSender", name="cart_set_sender", methods={"POST"})
     *
     */
    public function setSender(Request $request, Order $order): Response
    {
//        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(SenderType::class, $order->getSender());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //elobb elmentem a recipient formadatokat a Recipient tablaba
            $sender = $form->getData();
            $sender->setCustomer($order->getCustomer());  // a cimzettet egy Customerhez kotjuk
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sender);

//            $o = $order->getCurrentOrder();
            $order->setSender($sender);
            $entityManager->persist($order);

            $entityManager->flush();

            //hozzaadtom a current orderhez, az ujonnan rogzitett Sendert
//            $order->getCurrentOrder()->setSender($sender);

            $this->addFlash('success', 'A Sender sikeresen elmentve.');
        }

        return $this->redirectToRoute('site-checkout');
    }






    /**
     * @Route("/cart/setDiscount", name="cart_set_discount", methods={"POST"})
     */
    public function setDiscount(Request $request): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $discount = $this->getDoctrine()->getRepository('Discount')->findOneBy([
                'code' => $form->get('couponCode')->getData()
            ]);
            if ($discount !== null) {
                $this->get('App\Entity\OrderBuilder')->setDiscount($discount);
                $this->addFlash('success', 'Kedvezmény sikeresen aktiválva.');
            } else {
                $this->addFlash('danger', 'Kuponkón nem található');
            }
        }
        return $this->redirectToRoute('site-cart');
    }

    /**
     * @Route("/cart/empty", name="cart_empty", methods={"POST"})
     */
    public function clear(Request $request): Response
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(ClearCartType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->clear();
            $this->addFlash('success', 'A kosár sikeresen ürítve.');
        }
        return $this->redirectToRoute('homepage');
    }


    /**
     * Renders the dropdown cart. The items are retrieved from session
     */
    public function cartDetailsDropdown()
    {
        $orderBuilder = $this->get('App\Entity\OrderBuilder');
        return $this->render('webshop/site/navbar_cart_dropdown.html.twig', [
            'order' => $orderBuilder,
            'itemsInCart' => $orderBuilder->countItems(),
            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
        ]);
    }

    //    /**
//     * @Route("/cart/newRecipient", name="cart-new-recipient")
//     */
//    public function newRecipientForm(Request $request)
//    {
//        $orderBuilder = $this->get('App\Entity\OrderBuilder');
//        $recipient = new Recipient();
//        $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
//        $form = $this->createForm(AjaxRecipientType::class, $recipient);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $recipient = $form->getData();
//            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());  // a cimzettet egy Customerhez kotjuk
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($recipient);
//            $entityManager->flush();
//            /**
//             * If AJAX request, renders and returns an HTML form with the value
//             */
//            if ($request->isXmlHttpRequest()) {
//
//                return $this->render('webshop/cart/recipient_list.html.twig', [
//                    'recipients' => $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients(),
//                ]);
//            }
//        }
//        /**
//         * Renders form with errors
//         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
//         * (!?, there is a validation error)
//         */
//        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('webshop/cart/recipient_form.html.twig', [
//                'order' => $orderBuilder,
//                'recipientForm' => $form->createView(),
//            ]);
//            return new Response($html,400);
//
//        }
//
//        return $this->render('webshop/cart/recipient_form.html.twig', [
//            'order' => $orderBuilder,
//            'recipientForm' => $form->createView(),
//        ]);
//    }

}