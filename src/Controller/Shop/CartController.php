<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\CardCategory;
use App\Entity\ClientDetails;
use App\Entity\Model\DeliveryDate;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Geo\GeoPlace;
use App\Entity\Model\CustomerBasic;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\Model\CartCard;
use App\Entity\Model\MessageAndCustomer;
use App\Entity\Order;

use App\Entity\OrderBuilder;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\OrderItem;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\Shipping;
use App\Entity\Payment;

use App\Form\CartHiddenDeliveryDateFormType;
use App\Form\MessageAndCustomerFormType;
use App\Form\ShipAndPayFormType;
use App\Repository\GeoPlaceRepository;
use App\Validator\Constraints as AssertApp;

use App\Entity\User;
use App\Form\CartSelectDeliveryDateFormType;
use App\Form\CartSelectDeliveryIntervalType;
use App\Form\RecipientType;
use App\Form\CartAddItemType;
use App\Form\ClearCartType;
use App\Form\PaymentType;
use App\Form\NOTUSED;
use App\Form\SenderType;
use App\Form\MessageType;
use App\Form\RemoveItemType;
use App\Form\ItemQuantityType;
use App\Form\SetItemQuantityType;

use App\Form\CartStepOneType;
use App\Form\ShippingType;
use App\Form\SetDiscountType;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CartController extends AbstractController
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
     * @Route("/kosar", name="site-cart")
     */
    public function showCartPage()
    {
        // Ezt be kell szúrni a service.yaml-ba
        // App\Entity\OrderBuilder:
        //    public: true
        $orderBuilder = $this->orderBuilder;
        $customer = $this->getUser();  // equivalent of: $customer = $this->get('security.token_storage')->getToken()->getUser();

        $setDiscountForm = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());

        if (!$orderBuilder->hasItems()) {
            return $this->render('webshop/site/checkout_cart.html.twig', [
                'title' => 'Kosár',
                'order' => $orderBuilder,
                'setDiscountForm' => $setDiscountForm->createView(),
                'progressBar' => 'cart',
            ]);
        }
        $clearForm = $this->createForm(ClearCartType::class, $orderBuilder->getCurrentOrder());

        if ($orderBuilder->getCurrentOrder()->getDeliveryDate()) {
            $deliveryDate = new DeliveryDate($orderBuilder->getCurrentOrder()->getDeliveryDate()->format('Y-m-d'));
            $dateForm = $this->createForm(CartSelectDeliveryDateFormType::class, $deliveryDate);
        } else {
            $dateForm = $this->createForm(CartSelectDeliveryDateFormType::class);
        }

        //ez CSAK akkor kell ha nem renderelem bele a template-be!!
        $messageForm = $this->createForm(MessageType::class, $orderBuilder->getCurrentOrder());

        /**
         * After login, add current user as Customer (to the current Order and to the current Recipient also)
         */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /**
             * If before login a Recipient was added to the Order, asign the current Customer to this Recipient
             */
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($recipientInOrder);
                $entityManager->flush();
            }
        }

        /**
         * If Customer exists (is logged in), get all its Recipients and Senders
         */
        if ($customer) {
            $recipients = $customer->getRecipients();
//            dd($orderBuilder->getCurrentOrder()->getCustomer()->getRecipients());
        }
        /**
         *  Else, simply return the Recipiernt/Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $recipients = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getRecipient()) {
                $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
            }
        }

        $kategoria = $this->getDoctrine()->getRepository(ProductCategory::class)
            ->findBy(['slug' => 'ajandek']);
        $extras = $kategoria->getProducts();

        if ($recipients->isEmpty()) {
            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $recipientForm = $this->createForm(RecipientType::class, $recipient);

            return $this->render('webshop/site/checkout_cart.html.twig', [
                'title' => 'Kosár',
                'order' => $orderBuilder,
                'clearForm' => $clearForm->createView(),
                'setDiscountForm' => $setDiscountForm->createView(),
                'itemsInCart' => $orderBuilder->countItems(),
                'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
                'messageForm' => $messageForm->createView(),
                'recipientForm' => $recipientForm->createView(),
                'progressBar' => 'cart',
                'dateForm' => $dateForm->createView(),
//                'deliveryIntervalForm' => $deliveryIntervalForm->createView(),
            ]);
        }

        return $this->render('webshop/site/checkout_cart.html.twig', [
            'title' => 'Kosár',
            'order' => $orderBuilder,
            'clearForm' => $clearForm->createView(),
            'setDiscountForm' => $setDiscountForm->createView(),
            'itemsInCart' => $orderBuilder->countItems(),
            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
            'messageForm' => $messageForm->createView(),
            'recipients' => $recipients,
            'selectedRecipient' => null !== $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
            'progressBar' => 'cart',
            'dateForm' => $dateForm->createView(),
//            'deliveryIntervalForm' => $deliveryIntervalForm->createView(),
        ]);
    }

    /**
     * This is executed when 'site-checkout' page is loaded, either
     * directly by URL or by clicking GotoCheckout button on the Cart page.
     */
    public function validateCartPage(OrderBuilder $orderBuilder)
    {
        if (!$orderBuilder->hasRecipient()) {
            $this->addFlash('recipient-missing', 'Nem adtál meg címzettet!');
        }
        if (!$orderBuilder->hasDeliveryDate()) {
            $this->addFlash('date-missing', 'Add meg a szállítás idejét! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
        }
        if ($orderBuilder->isDeliveryDateInPast()) {
            $this->addFlash('date-missing', 'A szállítás napja hibás! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
        }
        if (!$orderBuilder->hasMessage()) {
            $this->addFlash('message-warning', 'Ha szeretnél üzenni a virággal, itt tudod kifejezni pár szóban, mit írjunk az üdvözlőlapra! (Nem kötelező)');
        }
        if (!$orderBuilder->hasRecipient() || !$orderBuilder->hasDeliveryDate() || $orderBuilder->isDeliveryDateInPast()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * This is executed when 'site-thankyou' page is loaded, either
     * directly by URL or by clicking Place Order button on the Checkout page.
     */
    public function validateOrder(OrderBuilder $orderBuilder)
    {
        if (!$orderBuilder->hasRecipient()) {
            $this->addFlash('recipient-missing', 'Nem adtál meg címzettet!');
        }
        if (!$orderBuilder->hasDeliveryDate()) {
            $this->addFlash('date-missing', 'Add meg a szállítás idejét! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
        }
        if ($orderBuilder->isDeliveryDateInPast()) {
            $this->addFlash('date-missing', 'A szállítás napja hibás! Bizonyosodj meg róla, hogy választottál szállítási idősávot is!');
        }
        if (!$orderBuilder->hasSender()) {
            $this->addFlash('sender-missing', 'Add meg a számlázási adatokat!');
        }
        if (!$orderBuilder->hasPayment()) {
            $this->addFlash('payment-missing', 'Válassz fizetési módot!');
        }
        if (!$orderBuilder->hasShipping()) {
            $this->addFlash('shipping-missing', 'Válassz szállítási módot!');
        }

        if (!$orderBuilder->hasRecipient() || !$orderBuilder->hasDeliveryDate() || $orderBuilder->isDeliveryDateInPast() ||
            !$orderBuilder->hasSender() || !$orderBuilder->hasPayment() || !$orderBuilder->hasShipping()) {
            return false;
        } else {
            return true;
        }
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
        $orderBuilder = $this->orderBuilder;

        if (!$orderBuilder->hasItems()) {
            return $this->redirectToRoute('site-cart');
        }

        if (!$this->validateCartPage($orderBuilder)) {
            return $this->redirectToRoute('site-cart');
        }

        $checkoutForm = $this->createForm(ShipAndPayFormType::class, $orderBuilder->getCurrentOrder());
//        $shippingForm = $this->createForm(ShippingType::class, $orderBuilder->getCurrentOrder());
//        $paymentForm = $this->createForm(PaymentType::class, $orderBuilder->getCurrentOrder());

        $shippingMethods = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAllOrdered();
        $paymentMethods = $this->getDoctrine()
            ->getRepository(Payment::class)
            ->findAllOrdered();

//        $senderForm = $this->createForm(SenderType::class, $orderBuilder->getCurrentOrder()->getSender());

        $customer = $this->getUser();  // equivalent of: $customer = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * After login, add current user as Customer (to the current Order and to the current Recipient also)
         */
        if ($customer) {
            $orderBuilder->setCustomer($customer);
            /**
             * If before login a Sender was added to the Order, asign the current Customer to this Sender
             */
            $senderInOrder = $orderBuilder->getCurrentOrder()->getSender();
            if ($senderInOrder) {
                $senderInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($senderInOrder);
                $entityManager->flush();
            }
            $recipientInOrder = $orderBuilder->getCurrentOrder()->getRecipient();
            if ($recipientInOrder) {
                $recipientInOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($recipientInOrder);
                $entityManager->flush();
            }
        }

        /**
         * If Customer exists (is logged in), get all its Senders
         */
        if ($customer) {
            $senders = $customer->getSenders();
        }
        /**
         *  Else, simply return the Sender saved already in the Order (This is the Guest Checkout scenario)
         */
        else {
            $senders = new ArrayCollection();
            if ($orderBuilder->getCurrentOrder()->getSender()) {
                $senders->add($orderBuilder->getCurrentOrder()->getSender());
            }
        }

        if ($senders->isEmpty()) {
            $sender = new Sender();
            $sender->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $senderForm = $this->createForm(SenderType::class, $sender);

            return $this->render('webshop/site/checkout_checkout.html.twig', [
                'title' => 'Pénztár',
                'order' => $orderBuilder,
                'form' => $checkoutForm->createView(),
                'shippingMethods' => $shippingMethods,
                'paymentMethods' => $paymentMethods,
//            'shippingForm' => $shippingForm->createView(),
//            'paymentForm' => $paymentForm->createView(),
                'senderForm' => $senderForm->createView(),
                'progressBar' => 'checkout',
            ]);
        }

        return $this->render('webshop/site/checkout_checkout.html.twig', [
            'title' => 'Pénztár',
            'order' => $orderBuilder,
            'form' => $checkoutForm->createView(),
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $paymentMethods,
//            'shippingForm' => $shippingForm->createView(),
//            'paymentForm' => $paymentForm->createView(),
            'senders' => $senders,
            'selectedSender' => null !== $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
            'progressBar' => 'checkout',
        ]);
    }

    /**
     * @Route("/penztar/koszonjuk", name="site-thankyou")
     */
    public function showThankyouPage()
    {
        $orderBuilder = $this->orderBuilder;
        if (!$this->validateOrder($orderBuilder)) {
            return $this->redirectToRoute('site-checkout');
        }
        $paymentMethod = $orderBuilder->getCurrentOrder()->getPayment()->isBankTransfer() ? true : false;

        return $this->render('webshop/cart/checkout-step4-thankyou.html.twig',[
            'title' => 'Sikeres rendelés!',
            'order' => $orderBuilder,
            'progressBar' => 'thankyou',
            'paymentMethod' => $paymentMethod,
        ]);
    }

//    /**
//     * @Route("/cart/setCheckout", name="cart-setCheckout", methods={"POST"}) //, "GET"
//     */
//    public function setCheckoutForm(Request $request): Response
//    {
//        $orderBuilder = $this->orderBuilder;
//        $form = $this->createForm(ShipAndPayFormType::class, $orderBuilder->getCurrentOrder());
//        $form->handleRequest($request);
//        if ($form->isSubmitted()) {
//            if ($form->getData()->getShipping()) {
//                $orderBuilder->setShipping($form->getData()->getShipping());
//            }
//            if ($form->getData()->getPayment()) {
//                $orderBuilder->setPayment($form->getData()->getPayment());
//            }
//            if ($form->isValid()) {
//                return $this->redirectToRoute('site-checkout');  // a regi checkout oldal, URL: /penztar
//            }
//        }
//
//
//        $shippingMethods = $this->getDoctrine()
//            ->getRepository(Shipping::class)
//            ->findAllOrdered();
//        $paymentMethods = $this->getDoctrine()
//            ->getRepository(Payment::class)
//            ->findAllOrdered();
//
//        $senderForm = $this->createForm(SenderType::class, $orderBuilder->getCurrentOrder()->getSender());
//
//        return $this->render('webshop/site/checkout_checkout.html.twig', [
//            'order' => $orderBuilder,
//            'form' => $form->createView(),
//            'shippingMethods' => $shippingMethods,
//            'paymentMethods' => $paymentMethods,
//            'senderForm' => $senderForm->createView(),
//        ]);
//    }

    /**
     * @Route("/cart/setDeliveryDate", name="cart-setDeliveryDate", methods={"POST", "GET"})
     *
     */
    public function setDeliveryDate(Request $request, HiddenDeliveryDate $date) //: Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(CartHiddenDeliveryDateFormType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $orderBuilder->setDeliveryDate($data->getDeliveryDate(), $data->getDeliveryInterval());
//            dd($data->getDeliveryFee());
            $orderBuilder->setDeliveryFee($data->getDeliveryFee());

            /**
             * If AJAX request, and because at this point the form data is processed, it returns Success (code 200)
             */
            if ($request->isXmlHttpRequest()) {
                return $this->redirectToRoute('site-checkout-step3-pickPayment');
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/hiddenDeliveryDate-form.html.twig', [
                'hiddenDateForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
    }

    /**
     * @Route("/cart/setShipping/{id}", name="cart-setShipping", methods={"POST"})
     */
    public function setShipping(Request $request, Shipping $shipping): Response
    {
        if ($request->isXmlHttpRequest()) {
            $orderBuilder = $this->orderBuilder;
            $orderBuilder->setShipping($shipping);
            $html = "All good";
            return new Response($html,200);
        } else {
            throw $this->createNotFoundException(
                'setShipping not allowed!'
            );
        }
    }

    /**
     * @Route("/cart/setPayment/{id}", name="cart-setPayment")
     */
    public function setPayment(Request $request, Payment $payment): Response
    {
        if ($request->isXmlHttpRequest()) {
            $orderBuilder = $this->orderBuilder;
            $orderBuilder->setPayment($payment);
            $html = "All good";
            return new Response($html,200);
        } else {
            throw $this->createNotFoundException(
                'setPayment not allowed!'
            );
        }
    }


//    /**
//     * @Route("/search/", name="cart-search-api", methods={"GET"})
//     */
//    public function searchPlacesApi(GeoPlaceRepository $geoRep, Request $request)
//    {
////        $geoPlace = $geoRep->findAllOrdered();
//        $geoPlaces = $geoRep->findAllMatching($request->query->get('query'));
//
//        return $this->json($geoPlaces,200, [], [
//            'groups' => ['main'],
//        ]);
//
//    }

//    /**
//     * Handles the Sender form. It is used to create and submit the form from JS.
//     *
//     * @Route("/cart/editRecipient/{id}", name="cart-editRecipient")
//     */
//    public function editRecipientForm(Request $request, ?Recipient $recipient, $id = null, ValidatorInterface $validator)
//    {
//        $orderBuilder = $this->orderBuilder;
//        $customer = $orderBuilder->getCurrentOrder()->getCustomer();
//        if (!$recipient) {
//            $recipient = new Recipient();
//            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
//            $form = $this->createForm(RecipientType::class, $recipient);
//        } else {
//            $form = $this->createForm(RecipientType::class, $recipient);
//        }
//        $form->handleRequest($request);
////        dd($form->isValid());
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            //elobb elmentem a recipient formadatokat a Recipient tablaba
//            $recipient = $form->getData();
//
//            $phone = $form->get('phone')->getData();
//
//
//            if ($orderBuilder->getCurrentOrder()->getCustomer()) {
//                $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer()); // a cimzettet egy Customerhez kotjuk
//            }
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($recipient);
//            $entityManager->flush();
//
//            $orderBuilder->setRecipient($recipient);
//
//            /**
//             * If AJAX request, returns the list of Recipients
//             */
//            if ($request->isXmlHttpRequest()) {
//                return $this->redirectToRoute('cart-getRecipients');
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
//        }
//
//        /**
//         * Renders form initially with data
//         */
//        return $this->render('webshop/cart/recipient_form.html.twig', [
//            'order' => $orderBuilder,
//            'recipientForm' => $form->createView(),
//        ]);
//    }

//    /**
//     * Handles the Sender form. It is used to create and submit the form from JS.
//     *
//     * @Route("/cart/editSender/{id}", name="cart-editSender")
//     */
//    public function editSenderForm(Request $request, ?Sender $sender, $id = null)
//    {
//        $orderBuilder = $this->orderBuilder;
//        $customer = $orderBuilder->getCurrentOrder()->getCustomer() ? $orderBuilder->getCurrentOrder()->getCustomer() : null;
//        if (!$sender) {
//            $sender = new Sender();
//            $sender->setCustomer($customer);
//            $form = $this->createForm(SenderType::class, $sender);
//        } else {
//            $form = $this->createForm(SenderType::class, $sender);
//        }
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            //elobb elmentem a sender formadatokat a Sender tablaba
//            $sender = $form->getData();
//            if ($customer) {
//                $sender->setCustomer($customer); // a feladót egy Customerhez kotjuk
//            }
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($sender);
//            $entityManager->flush();
//
//            $orderBuilder->setSender($sender);
//
//            /**
//             * If AJAX request, returns the list of Recipients
//             */
//            if ($request->isXmlHttpRequest()) {
//                return $this->redirectToRoute('cart-getSenders');
//            }
//        }
//        /**
//         * Renders form with errors
//         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
//         * (!?, there is a validation error)
//         */
//        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('webshop/cart/sender_form.html.twig', [
//                'order' => $orderBuilder,
//                'senderForm' => $form->createView(),
//            ]);
//            return new Response($html,400);
//
//        }
////        if ($request->isXmlHttpRequest()) {
////            return $this->render('webshop/cart/recipient_form.html.twig', [
////                'order' => $orderBuilder,
////                'recipientForm' => $form->createView(),
////            ]);
////        }
//        /**
//         * Renders form initially with data
//         */
//        return $this->render('webshop/cart/sender_form.html.twig', [
//            'order' => $orderBuilder,
//            'senderForm' => $form->createView(),
//        ]);
//    }

//    /**
//     * Gets the City and Province based on the Zip code entered by the user.
//     * Returns a JSON response, with a 'success' field set to 'true' if results were found, otherwise returns 'false'.
//     *
//     * It's used in the Recipient and Sender forms, via JS.
//     *
//     * @Route("/cart/getPlaceByZip/{zip}", name="cart-getPlaceByZip", methods={"GET"})
//     */
//    public function getPlaceByZip(Request $request, int $zip = null)
//    {
//        if (!$zip) {
//            $zip = $request->query->get('zip');
//        }
//        if ($zip) {
//            $place = $this->getDoctrine()->getRepository(GeoPlace::class)
//                ->findOneBy(['zip' => $zip]);
//        }
//
//        try {
//            if (null !== $place && '' !== $place) {
//                return new JsonResponse([
//                    'success' => true,
//                    'city' => '' === $place->getDistrict() || null === $place->getDistrict() ? $place->getCity() : $place->getCity().' - '.$place->getDistrict().' kerület',
//                    'province' => $place->getProvince(),
//                ]);
//            } else {
//                throw new Exception('GeoPlace does not exist!');
//            }
//        } catch (\Exception $exception) {
//            return new JsonResponse([
//                'success' => false,
//                'code' => $exception->getCode(),
//                'message' => $exception->getMessage(),
//            ]);
//        }
//    }

//    /**
//     * Gets the list of Recipients. Handles 2 situations:
//     *
//     * 1. A Customer is assigned to the current order (User is logged in)
//     * Returns all the customer's recipients.
//     *
//     * 2. No customer was found in the current order (User isn't logged in, eg: Guest checkout)
//     * In this case returns only one Recipient.
//     *
//     * @Route("/cart/getRecipients", name="cart-getRecipients")
//     */
//    public function getRecipients()
//    {
//        $orderBuilder = $this->orderBuilder;
//        /**
//         * If the Order has a Customer, returns the list of the customer's Recipients
//         */
//        if ($orderBuilder->getCurrentOrder()->getCustomer()) {
//            $recipients = $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients();
//        }
//        /**
//         * Else simply returns the Recipient from within the Order (Checkout whithout user registration)
//         */
//        else {
//            $recipients = new ArrayCollection();
//            $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
//        }
//
//        return $this->render('webshop/cart/recipient_list.html.twig', [
//            'recipients' => $recipients,
//            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
//        ]);
//    }

//    /**
//     * Gets the list of Senders. Handles 2 situations, see getRecipients()
//     *
//     * @Route("/cart/getSenders", name="cart-getSenders")
//     */
//    public function getSenders()
//    {
//        $orderBuilder = $this->orderBuilder;
//        /**
//         * If the Order has a Customer, returns the list of the customer's Senders
//         */
//        if ($orderBuilder->getCurrentOrder()->getCustomer()) {
//            $senders = $orderBuilder->getCurrentOrder()->getCustomer()->getSenders();
//        }
//        /**
//         * Else, simply returns the Sender saved already in the Order (This is the Guest Checkout scenario)
//         */
//        else {
//            $senders = new ArrayCollection();
//            $senders->add($orderBuilder->getCurrentOrder()->getSender());
////            dd($senders);
//        }
//
//        return $this->render('webshop/cart/sender_list.html.twig', [
//            'senders' => $senders,
//            'selectedSender' => $orderBuilder->getCurrentOrder()->getSender() ? $orderBuilder->getCurrentOrder()->getSender()->getId() : null,
//        ]);
//    }

//    /**
//     * Picks a Recipient from the recipient list and assigns it to the current Order.
//     * It is used in JS.
//     *
//     * @Route("/cart/pickRecipient/{id}", name="cart-pickRecipient")
//     */
//    public function pickRecipient(Request $request, Recipient $recipient)
//    {
//        $orderBuilder = $this->orderBuilder;
//        $orderBuilder->setRecipient($recipient);
//        $html = $this->render('admin/item.html.twig', [
//            'item' => 'Címzett sikeresen kiválasztva!',
//        ]);
//        return new Response($html, 200);
//    }

//    /**
//     * Picks a Sender from the sender list and assigns it to the current Order.
//     * It is used in JS.
//     *
//     * @Route("/cart/pickSender/{id}", name="cart-pickSender")
//     */
//    public function pickSender(Request $request, Sender $sender)
//    {
//        $orderBuilder = $this->orderBuilder;
//        $orderBuilder->setSender($sender);
//        $html = $this->render('admin/item.html.twig', [
//            'item' => 'Számlázási címzett sikeresen kiválasztva!',
//        ]);
//        return new Response($html, 200);
//    }




    /**
     * NINCS HASZNALVA !!!!
     * Ez volt hasznalva, amikor formkent jelentek meg a szallitasi datumok
     */
//     /**
//     *
//     * @Route("/cart/setDeliveryDate", name="cart-setDeliveryDate", methods={"POST", "GET"})
//     *
//     */
//    public function setDeliveryDate(Request $request, ?DeliveryDate $date) //: Response
//    {
//        $orderBuilder = $this->orderBuilder;
//        $form = $this->createForm(CartSelectDeliveryDateFormType::class, $date);
//        $form->handleRequest($request);
////        dd($form->getData());
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $data = $form->getData();
//            if ($data) {
//                $date = $data->getDeliveryDate();
//                $interval = $data->getDeliveryInterval();
////                $orderBuilder->setDeliveryDate(\DateTime::createFromFormat('!Y-m-d',$date), $interval);
//                $orderBuilder->setDeliveryDate($date, $interval);
//
//                /**
//                 * If AJAX request, and because at this point the form data is processed, it redirects to Step2.
//                 * Ez nem kerul exekutálásra, mivel JS kódból van átírányítva a köv. oldalra. //// Nem ertem miert????
//                 */
////                if ($request->isXmlHttpRequest()) {
////                    return $this->redirectToRoute('site-checkout-step3-pickPayment');
////                }
//            }
//            return $this->render('/webshop/cart/deliveryDate-form.html.twig',[
//                'dateForm' => $form->createView(),
//            ]);
//        }

//        /**
//         * Renders form with errors
//         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
//         * (!?, there is a validation error)
//         */
//        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('/webshop/cart/deliveryDate-form.html.twig', [
//                'dateForm' => $form->createView(),
//            ]);
//            return new Response($html,400);
//
//        }

//        if ($request->isXmlHttpRequest()) {
//            return $this->render('/webshop/cart/deliveryDate-form.html.twig', [
//                'dateForm' => $form->createView(),
//            ]);
//        }



//        return $this->redirectToRoute('site-cart');
//        else {
//            throw $this->createNotFoundException(
//                'Nem tudja mit csináljon, ha nem valid a deliveryDate form!'
//            );
//        }
//
//        return $this->render('/webshop/cart/deliveryDate-form.html.twig',[
//            'dateForm' => $form->createView(),
//        ]);
//    }



    /**
     * Saves the data entered in MessageAndCustomer form
     * It is used in JS.
     *
     * @Route("/cart/setMessageAndCustomer", name="cart-setMessageAndCustomer", methods={"POST"})
     */
    public function setMessageAndCustomer(Request $request, ?MessageAndCustomer $messageAndCustomer) //: Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(MessageAndCustomerFormType::class, $messageAndCustomer); //,
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MessageAndCustomer $data */
            $data = $form->getData();
            $orderBuilder->setMessage($data->getCard());
            $orderBuilder->setCustomerBasic($data->getCustomer());
                /**
                 * If AJAX request, and because at this point the form data is processed, it redirects to Step2.
                 * Ez nem kerul exekutálásra, mivel JS kódból van átírányítva a köv. oldalra. //// Nem ertem miert????
                 */
                if ($request->isXmlHttpRequest()) {
                    return $this->redirectToRoute('site-checkout-step2-pickRecipient');
                }
        }

        $cardCategories = $this->getDoctrine()->getRepository(CardCategory::class)
            ->findAll();

        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/message-and-customer-form.html.twig', [
                'messageAndCustomerForm' => $form->createView(),
                'order' => $orderBuilder->getId(),
                'cardCategories' => $cardCategories,
            ]);
            return new Response($html,400);

        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('webshop/cart/message-and-customer-form.html.twig', [
                'messageAndCustomerForm' => $form->createView(),
                'order' => $orderBuilder->getId(),
                'cardCategories' => $cardCategories,
            ]);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/message-and-customer-form.html.twig', [
            'messageAndCustomerForm' => $form->createView(),
            'order' => $orderBuilder->getId(),
            'cardCategories' => $cardCategories,
        ]);
    }


    /**
     * Used on the Product page to add a product as an Item to the Order.
     * Adds the:
     *      - product
     *      - subproduct
     *      - deliveryDate (without deliveryInterval)
     *
     * @Route("/cart/addItem/{id}", name="cart-addItem", methods={"POST"})
     */
    public function addItem(Request $request, Product $product): Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(CartAddItemType::class, $product);
        $form->handleRequest($request);

//        dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            $orderBuilder->addItem($product, $form->get('quantity')->getData());
            $deliveryDate = $form->get('deliveryDate')->get('deliveryDate')->getData();
            $orderBuilder->setDeliveryDate($deliveryDate ? $deliveryDate : null, null);


            $clientDetails = new ClientDetails($request->getClientIp(), $request->headers->get('user-agent'), $request->headers->get('accept-language'));
            $orderBuilder->setClientDetails($clientDetails);

            return $this->redirectToRoute('site-checkout-step1-pickExtraGift');
        } else {
            throw $this->createNotFoundException(
                'A form hibás >> invalid form!'
            );
        }
    }
    
    /**
     * Used on the Checkout Step1 page to add a gift product as an Item to the Order.
     *
     * @Route("/cart/addGift/{id}", name="cart-addGift", methods={"POST"})
     */
    public function addGiftItem(Request $request, Product $product, $id = null)
    {
        $orderBuilder = $this->orderBuilder;
        $orderBuilder->addItem($product, 1);
        $showQuantity = true;
        return $this->render('webshop/cart/_cart-items-withSummary-widget.html.twig', [
            'order' => $orderBuilder,
            'showQuantity' => $showQuantity,
        ]);
    }

    /**
     * Removes an item from the cart. Used in JS.
     *
     * @Route("/cart/removeItemFromCart/{id}/{showQuantity}", name="cart-removeItem")
     */
    public function removeItemFromCart(Request $request, OrderItem $item, bool $showQuantity = false): Response
    {
        $orderBuilder = $this->orderBuilder;
        $orderBuilder->removeItem($item);
        if ($request->isXmlHttpRequest()) {
            return $this->render('webshop/cart/_cart-items-withSummary-widget.html.twig', [
                'order' => $orderBuilder,
                'showQuantity' => $showQuantity,
            ]);
        }
        return $this->render('webshop/cart/_cart-items-withSummary-widget.html.twig', [
            'order' => $orderBuilder,
        ]);
    }

    /**
     * Creates the dropdown form in the cart, used to change quantity.
     * It is used within the template with 'generate'.
     * Apears in the Cart page and the sidebar cart too.
     */
    public function setItemQuantityForm(OrderItem $item): Response
    {
        $form = $this->createForm(SetItemQuantityType::class, $item);
        return $this->render('webshop/cart/_setItemQuantity_form.html.twig', [
            'quantityForm' => $form->createView()
        ]);
    }

    /**
     * Updates the quantity value to the Item in the current Order.
     *
     * @Route("/cart/setItemQuantity/{id}", name="cart-setItemQuantity", methods={"POST"})
     */
    public function setItemQuantity(Request $request, OrderItem $item): Response
    {
        $form = $this->createForm(SetItemQuantityType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->orderBuilder->setItemQuantity($item, $form->getData()->getQuantity());
            /**
             * If AJAX request, renders and returns an HTML form with the value
             */
            if ($request->isXmlHttpRequest()) {

                return $this->render('webshop/cart/_setItemQuantity_form.html.twig', [
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
     * NO LONGER USED!!!!
     * It was used when there was no CustomerBasic form in Step1
     */
    public function createMessageForm(): Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(MessageType::class, $orderBuilder->getCurrentOrder());

        return $this->render('webshop/cart/message_form.html.twig', [
            'messageForm' => $form->createView(),
            'order' => $orderBuilder->getId()
        ]);
    }

    /**
     * NO LONGER USED!!!!
     * It was used when there was no CustomerBasic form in Step1
     *
     * Saves the data entered in Message fields
     *
     * @Route("/cart/setMessage", name="cart-setMessage", methods={"POST", "GET"})
     */
    public function setMessage(Request $request) //: Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);
//        dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Ha mindkét mező ki van töltve, akkor elmentem,
             * Ha mindkét mező üres (feltételezem direkt hagyta üresen), akkor engedem tovább,
             * amúgy meg megnézem melyik mező maradt üressen és hibát rendelek hozzá.
             */
//            dd($form->get('message')->isEmpty());
            if ( (!$form->get('message')->isEmpty() && !$form->get('messageAuthor')->isEmpty()) ||
                        ($form->get('message')->isEmpty() && $form->get('messageAuthor')->isEmpty()) ) {
                $orderBuilder->setMessageAndAuthor($form->get('message')->getData(),$form->get('messageAuthor')->getData());
                /**
                 * If AJAX request and since at this point the data is saved to db, it redirects to Checkout.
                 * Ez nem kerul exekutálásra, mivel JS kódból van átírányítva a köv. oldalra.
                 */
                if ($request->isXmlHttpRequest()) {
                    return $this->redirectToRoute('homepage');
                }
            } else {
                if ($form->get('message')->isEmpty()) {
                    $form->get('message')->addError(new FormError('Az üzenet lemaradt!'));
                } else {
                    $form->get('messageAuthor')->addError(new FormError('Nem írtad alá az üzenetet!'));
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

        if ($request->isXmlHttpRequest()) {
            return $this->render('webshop/cart/message_form.html.twig', [
                'messageForm' => $form->createView(),
                'order' => $orderBuilder->getId()
            ]);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/message_form.html.twig', [
            'messageForm' => $form->createView(),
            'order' => $orderBuilder->getId()
        ]);
    }

//    /**
//     * NO LONGER USED!!!!
//     * This came with the original script!!!
//     *
//     * Creates an empty form with Sender fields
//     */
//    public function createSenderForm(Order $order): Response
//    {
//        $form = $this->createForm(SenderType::class, $order->getSender());
//
//        //megmondom a formnak a Customer-t (az ID-jat)
//        $form->get('customer')->setData($order->getCustomer()->getId());
//
//        return $this->render('webshop/cart/sender_form.html.twig', [
//            'senderForm' => $form->createView()
//        ]);
//    }

//    /**
//     * NO LONGER USED!!!!
//     * Saves the data entered in Sender fields
//     *
//     * param int $id
//     *
//     * @Route("/cart/setSenderr", name="cart_set_sender", methods={"POST"})
//     *
//     */
//    public function setSender(Request $request, Order $order): Response
//    {
////        $order = $this->orderBuilder;
//        $form = $this->createForm(SenderType::class, $order->getSender());
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            //elobb elmentem a sender formadatokat a Sender tablaba
//            $sender = $form->getData();
//            $sender->setCustomer($order->getCustomer());  // a sendert egy Customerhez kotjuk
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($sender);
//
//            $order->setSender($sender);
//            $entityManager->persist($order);
//            $entityManager->flush();
//
//            $this->addFlash('success', 'A Sender sikeresen elmentve.');
//        }
//
//        return $this->redirectToRoute('site-checkout');
//    }



    /**
     * @Route("/cart/setDiscount", name="cart_set_discount", methods={"POST"})
     */
    public function setDiscount(Request $request): Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(SetDiscountType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $discount = $this->getDoctrine()->getRepository('Discount')->findOneBy([
                'code' => $form->get('couponCode')->getData()
            ]);
            if ($discount !== null) {
                $this->orderBuilder->setDiscount($discount);
                $this->addFlash('success', 'Kedvezmény sikeresen aktiválva.');
            } else {
                $this->addFlash('danger', 'Kuponkón nem található');
            }
        }
        return $this->redirectToRoute('site-cart');
    }

    /**
     * NO LONGER USED!!!!
     * This came with the original script!!!
     *
     * @Route("/cart/empty", name="cart_empty", methods={"POST"})
     */
    public function clear(Request $request): Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(ClearCartType::class, $orderBuilder->getCurrentOrder());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->orderBuilder->clear();
            $this->addFlash('success', 'A kosár sikeresen ürítve.');
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * NO LONGER USED!!!!
     * It places the products in a Owl slider !!!!
     *
     * Renders the Pick A Gift module in the cart page.
     */
    public function pickAGift()
    {
        $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
        return $this->render('webshop/cart/gift_widget.html.twig', ['termekek' => $products]);
    }

    /**
     * NO LONGER USED!!!!
     * It used to create the form in the Product page with 'render' used within the template.
     */
    public function addItemForm(Product $product): Response
    {
        $form = $this->createForm(CartAddItemType::class, $product);

        return $this->render('webshop/site/_addItem_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}