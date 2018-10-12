<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\OrderItem;

use App\Entity\Recipient;
use App\Form\CartAddItemType;
use App\Form\ClearCartType;
use App\Form\RecipientType;
use App\Form\SenderType;
use App\Form\MessageType;
use App\Form\RemoveItemType;
use App\Form\SetItemQuantityType;

use App\Form\CartStepOneType;
use App\Form\SetDiscountType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CartController extends Controller
{

    /**
     * @Route("/kosar", name="site_cart")
     */
    public function showCartPage()
    {
        // Ezt be kell szúrni a service.yaml-ba
        // App\Entity\OrderBuilder:
        //    public: true

        $order = $this->get('App\Entity\OrderBuilder');
        $clearForm = $this->createForm(ClearCartType::class, $order->getCurrentOrder());
        //$setPaymentForm = $this->createForm(SetPaymentType::class, $order->getCurrentOrder());
        //$setShippingForm = $this->createForm(SetShippingType::class, $order->getCurrentOrder());
        $setDiscountForm = $this->createForm(SetDiscountType::class, $order->getCurrentOrder());
        $recipientForm = $this->createForm(RecipientType::class, $order->getCurrentOrder()->getRecipient());
        $messageForm = $this->createForm(MessageType::class, $order->getCurrentOrder());

        return $this->render('webshop/site/checkout_cart.html.twig', [
            'order' => $order,
            'clearForm' => $clearForm->createView(),
//            'setPaymentForm' => $setPaymentForm->createView(),
//            'setShippingForm' => $setShippingForm->createView(),
            'setDiscountForm' => $setDiscountForm->createView(),
            'itemsInCart' => $order->countItems(),
            'totalAmountToPay' => $order->summary()->getTotalAmountToPay(),
            'messageForm' => $messageForm->createView(),
            'form' => $recipientForm->createView(),
        ]);
    }

    /**
     * @Route("/penztar", name="site_checkout")
     */
    public function showCheckoutPage()
    {
        // Ezt be kell szúrni a service.yaml-ba
        // App\Entity\OrderBuilder:
        //    public: true

        $order = $this->get('App\Entity\OrderBuilder');
        //$setPaymentForm = $this->createForm(SetPaymentType::class, $order->getCurrentOrder());
        //$setShippingForm = $this->createForm(SetShippingType::class, $order->getCurrentOrder());
        $senderForm = $this->createForm(SenderType::class, $order->getCurrentOrder()->getSender());

        return $this->render('webshop/site/checkout_checkout.html.twig', [
            'order' => $order,
//            'setPaymentForm' => $setPaymentForm->createView(),
//            'setShippingForm' => $setShippingForm->createView(),
            'senderForm' => $senderForm->createView(),
        ]);
    }


    /**
     * Renders the dropdown cart. The items are retrieved from session
     */
    public function cartDetailsDropdown()
    {
        $order = $this->get('App\Entity\OrderBuilder');
        return $this->render('webshop/site/navbar_cart_dropdown.html.twig', [
            'order' => $order,
            'itemsInCart' => $order->countItems(),
            'totalAmountToPay' => $order->summary()->getTotalAmountToPay(),
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
    
    public function setItemQuantityForm(OrderItem $item): Response
    {
        $form = $this->createForm(SetItemQuantityType::class, $item);
        return $this->render('webshop/site/_setItemQuantity_form.html.twig', [
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

        return $this->redirectToRoute('site_cart');
    }

    /**
     * @Route("/cart/removeItem/{id}", name="cart_remove_item", methods={"POST"})
     */
    public function removeItem(Request $request, OrderItem $item): Response
    {
        $form = $this->createForm(RemoveItemType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->removeItem($item);
            $this->addFlash('success', 'A termék sikeresen törölve.');
        }
        return $this->redirectToRoute('site_cart');
    }

    /**
     * @Route("/cart/setItemQuantity/{id}", name="cart_set_item_quantity", methods={"POST"})
     */
    public function setQuantity(Request $request, OrderItem $item): Response
    {
        $form = $this->createForm(SetItemQuantityType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setItemQuantity($item, $form->getData()->getQuantity());
            $this->addFlash('success', 'A mennyiség sikeresen módosítva.');
        }
        return $this->redirectToRoute('site_cart');
    }
    /**
     * @Route("/cart/empty", name="cart_empty", methods={"POST"})
     */
    public function clear(Request $request): Response
    {
        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(ClearCartType::class, $order->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->clear();
            $this->addFlash('success', 'A kosár sikeresen ürítve.');
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * Creates an empty form with Recipient fields
     */
    public function createRecipientForm(Order $order): Response
    {
        $form = $this->createForm(RecipientType::class, $order->getRecipient());

        return $this->render('webshop/cart/recipient_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Saves the data entered in Recipient fields
     *
     * param int $id
     *
     * @Route("/cart/setRecipient", name="cart_set_recipient", methods={"POST"})
     *
     */
    public function setRecipient(Request $request, Order $order): Response
    {
//        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(RecipientType::class, $order->getRecipient());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            //elobb elmentem a recipient formadatokat a Recipient tablaba
            $recipient = $form->getData();
            $recipient->setCustomer($order->getCustomer());  // a cimzettet egy Customerhez kotjuk
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipient);

            $order->setRecipient($recipient);
            $entityManager->persist($order);

            $entityManager->flush();

            //hozzaadtom a current orderhez, az ujonnan rogzitett recipientet ($cimzett)
//            $order->getCurrentOrder()->setRecipient($recipient);

            $this->addFlash('success', 'A Recipient sikeresen elmentve.');
        }

        return $this->redirectToRoute('site_cart');
    }

    public function createMessageForm(): Response
    {
        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(MessageType::class, $order->getCurrentOrder());

        return $this->render('webshop/cart/message_form.html.twig', [
            'messageForm' => $form->createView(),
            'order' => $order->getId()
        ]);
    }

    /**
     * Saves the data entered in Message fields
     *
     * param int $id
     *
     * @Route("/cart/setMessage", name="cart_set_message", methods={"POST"})
     *
     */
    public function setMessage(Request $request): Response
    {
        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(MessageType::class, $order->getCurrentOrder());
        $form->handleRequest($request);
//        dump($form);die;
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setMessageAndAuthor($form->getData()->getMessage(),$form->getData()->getMessageAuthor());

//            $order->getCurrentOrder()->setMessage($form->getData()->getMessage());
//            $order->getCurrentOrder()->setMessageAuthor($form->getData()->getMessageAuthor());

            $this->addFlash('success', 'A Message sikeresen elmentve.');
        }

        return $this->redirectToRoute('site_cart');
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

        return $this->redirectToRoute('site_checkout');
    }




    /**
     * @Route("/cart/setPayment", name="cart_set_payment", methods={"POST"})
     */
    public function setPayment(Request $request): Response
    {
        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(SetPaymentType::class, $order->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setPayment($form->getData()->getPayment());
            $this->addFlash('success', 'A fizetési mód sikeresen beállítva.');
        }
        return $this->redirectToRoute('site_cart');
    }
    /**
     * @Route("/cart/setShipping", name="cart_set_shipping", methods={"POST"})
     */
    public function setShipping(Request $request): Response
    {
        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(SetShippingType::class, $order->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('App\Entity\OrderBuilder')->setShipping($form->getData()->getShipping());
            $this->addFlash('success', 'A szállítási mód sikeresen beállítva.');
        }
        return $this->redirectToRoute('site_cart');
    }
    /**
     * @Route("/cart/setDiscount", name="cart_set_discount", methods={"POST"})
     */
    public function setDiscount(Request $request): Response
    {
        $order = $this->get('App\Entity\OrderBuilder');
        $form = $this->createForm(SetDiscountType::class, $order->getCurrentOrder());
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
        return $this->redirectToRoute('site_cart');
    }

}