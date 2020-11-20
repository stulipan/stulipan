<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\GreetingCardMessageCategory;
use App\Entity\ClientDetails;
use App\Entity\Model\DeliveryDate;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Geo\GeoPlace;
use App\Entity\Model\CustomerBasic;
use App\Entity\Model\HiddenDeliveryDate;
use App\Model\CartGreetingCard;
use App\Model\CheckoutPaymentMethod;
use App\Model\CheckoutRecipientAndCustomer;
use App\Model\CheckoutShippingMethod;
use App\Entity\Order;

use App\Entity\OrderBuilder;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\OrderItem;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\ShippingMethod;
use App\Entity\PaymentMethod;

use App\Form\DeliveryDate\CartHiddenDeliveryDateFormType;
use App\Form\Checkout\PaymentMethodType;
use App\Form\CustomerBasic\CustomerBasicsFormType;
use App\Repository\GeoPlaceRepository;
use App\Validator\Constraints as AssertApp;

use App\Entity\User;
use App\Form\DeliveryDate\CartSelectDeliveryDateFormType;
use App\Form\Cart\CartSelectDeliveryIntervalType;
use App\Form\RecipientType;
use App\Form\AddToCart\CartAddItemType;
use App\Form\Cart\ClearCartType;
use App\Form\SenderType;
use App\Form\GreetingCard\GreetingCardFormType;
use App\Form\Cart\SetItemQuantityType;

use App\Form\Checkout\ShippingMethodType;
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
use Symfony\Contracts\Translation\TranslatorInterface;


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
     * Handles the CustomerBasic form.
     * Submit the form from AJAX.
     *
     * @Route("/cart/setCustomer", name="cart-setCustomer", methods={"POST"})
     */
    public function setCustomer(Request $request, ?CustomerBasic $customer)
    {

        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(CustomerBasicsFormType::class, $customer); //,
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CustomerBasic $data */
            $data = $form->getData();
            $orderBuilder->setCustomerBasic($data);

            //
            $customer = $this->getUser();
            if ($customer) {
                $customer->setPhone($data->getPhone());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($customer);
                $entityManager->flush();
            }
        }

        /** Renders the form with errors */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/user-basicDetails-form.html.twig', [
                'customerForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/user-basicDetails-form.html.twig', [
            'customerForm' => $form->createView(),
        ]);
    }


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
//                return $this->redirectToRoute('site-checkout-step3-pickPayment');
                return $this->render('webshop/cart/hidden-delivery-date-form.html.twig', [
                    'hiddenDateForm' => $form->createView(),
                ]);
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/hidden-delivery-date-form.html.twig', [
                'hiddenDateForm' => $form->createView(),
            ]);
            return new Response($html,400);
//            return new Response($html,200);
        }
    }

    /**
     * @Route("/cart/setShippingMethod", name="cart-setShippingMethod", methods={"POST"})
     */
    public function setShippingMethod(Request $request, CheckoutShippingMethod $shippingMethod): Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(ShippingMethodType::class, $shippingMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderBuilder->setShippingMethod($form->getData()->getShippingMethod());
        }
        /** Renders form with errors */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/shipping-method-form.html.twig', [
                'shippingMethodForm' => $form->createView(),
//                'shippingMethods' => $this->getDoctrine()->getRepository(Shipping::class)->findAll(),
                'shippingMethods' => $this->getDoctrine()->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
            ]);
            return new Response($html,400);
        }
        return $this->render('webshop/cart/shipping-method-form.html.twig', [
            'shippingMethodForm' => $form->createView(),
            'shippingMethods' => $this->getDoctrine()->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
        ]);


//        if ($request->isXmlHttpRequest()) {
//            $orderBuilder = $this->orderBuilder;
//            $orderBuilder->setShippingMethod($shipping);
//            $html = "All good";
//            return new Response($html,200);
//        } else {
//            throw $this->createNotFoundException(
//                'setShipping not allowed!'
//            );
//        }
    }

    /**
     * @Route("/cart/setPaymentMethod", name="cart-setPaymentMethod", methods={"POST"})
     */
    public function setPaymentMethod(Request $request, CheckoutPaymentMethod $paymentMethod): Response
    {
        if ($request->isXmlHttpRequest()) {
            $orderBuilder = $this->orderBuilder;
            $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $orderBuilder->setPaymentMethod($form->getData()->getPaymentMethod());
            }
            /** Renders form with errors */
            if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
                $html = $this->renderView('webshop/cart/payment-method-form.html.twig', [
                    'paymentMethodForm' => $form->createView(),
//                    'paymentMethods' => $this->getDoctrine()->getRepository(Shipping::class)->findAll(),
                    'paymentMethods' => $this->getDoctrine()->getRepository(PaymentMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
                ]);
                return new Response($html,400);
            }
            return $this->render('webshop/cart/payment-method-form.html.twig', [
                'paymentMethodForm' => $form->createView(),
                'paymentMethods' => $this->getDoctrine()->getRepository(PaymentMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
            ]);
        } else {
            throw $this->createNotFoundException(
                'setPaymentMethod not allowed!'
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


//    /**
//     * Saves the data entered in RecipientAndCustomer form (Checkout Step 1 Delivery Address)
//     * It is used in JS.
//     *
//     * @Route("/cart/setRecipientAndCustomer", name="cart-setRecipientAndCustomer", methods={"POST"})
//     */
//    public function setRecipientAndCustomer(Request $request, ?RecipientAndCustomer $recipientAndCustomer) //: Response
//    {
//        $orderBuilder = $this->orderBuilder;
//        $form = $this->createForm(RecipientAndCustomerFormType::class, $recipientAndCustomer); //,
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            /** @var RecipientAndCustomer $data */
//            $data = $form->getData();
//            $orderBuilder->setRecipient($data->getRecipient());
//            $orderBuilder->setCustomerBasic($data->getCustomer());
//            /**
//             * If AJAX request, and because at this point the form data is processed, it redirects to Step2.
//             * Ez nem kerul exekutálásra, mivel JS kódból van átírányítva a köv. oldalra. //// Nem ertem miert????
//             */
//            if ($request->isXmlHttpRequest()) {
//                return $this->redirectToRoute('site-checkout-step2-pickShipping');
//            }
//        }
//
//        /**
//         * Renders form with errors
//         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
//         * (!?, there is a validation error)
//         */
//        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('webshop/cart/message-and-customer-form.html.twig', [   /// Eddig jutottam
//                'messageAndCustomerForm' => $form->createView(),
//                'order' => $orderBuilder->getId(),
//                'cardCategories' => $cardCategories,
//            ]);
//            return new Response($html,400);
//
//        }
//
//        if ($request->isXmlHttpRequest()) {
//            return $this->render('webshop/cart/message-and-customer-form.html.twig', [
//                'messageAndCustomerForm' => $form->createView(),
//                'order' => $orderBuilder->getId(),
//                'cardCategories' => $cardCategories,
//            ]);
//        }
//
//        /**
//         * Renders form initially with data
//         */
//        return $this->render('webshop/cart/message-and-customer-form.html.twig', [
//            'messageAndCustomerForm' => $form->createView(),
//            'order' => $orderBuilder->getId(),
//            'cardCategories' => $cardCategories,
//        ]);
//    }

//    /**
//     * Saves the data entered in MessageAndCustomer form
//     * It is used in JS.
//     *
//     * @Route("/cart/setMessageAndCustomer", name="cart-setMessageAndCustomer", methods={"POST"})
//     */
//    public function setMessageAndCustomer(Request $request, ?MessageAndCustomer $messageAndCustomer) //: Response
//    {
//        $orderBuilder = $this->orderBuilder;
//        $form = $this->createForm(MessageAndCustomerFormType::class, $messageAndCustomer); //,
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            /** @var MessageAndCustomer $data */
//            $data = $form->getData();
//            $orderBuilder->setMessage($data->getCard());
//            $orderBuilder->setCustomerBasic($data->getCustomer());
//                /**
//                 * If AJAX request, and because at this point the form data is processed, it redirects to Step2.
//                 * Ez nem kerul exekutálásra, mivel JS kódból van átírányítva a köv. oldalra. //// Nem ertem miert????
//                 */
//                if ($request->isXmlHttpRequest()) {
//                    return $this->redirectToRoute('site-checkout-step1-pickDeliveryAddress');
//                }
//        }
//
//        $cardCategories = $this->getDoctrine()->getRepository(CardCategory::class)
//            ->findAll();
//
//        /**
//         * Renders form with errors
//         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
//         * (!?, there is a validation error)
//         */
//        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('webshop/cart/message-and-customer-form.html.twig', [
//                'messageAndCustomerForm' => $form->createView(),
//                'order' => $orderBuilder->getId(),
//                'cardCategories' => $cardCategories,
//            ]);
//            return new Response($html,400);
//
//        }
//
//        if ($request->isXmlHttpRequest()) {
//            return $this->render('webshop/cart/message-and-customer-form.html.twig', [
//                'messageAndCustomerForm' => $form->createView(),
//                'order' => $orderBuilder->getId(),
//                'cardCategories' => $cardCategories,
//            ]);
//        }
//
//        /**
//         * Renders form initially with data
//         */
//        return $this->render('webshop/cart/message-and-customer-form.html.twig', [
//            'messageAndCustomerForm' => $form->createView(),
//            'order' => $orderBuilder->getId(),
//            'cardCategories' => $cardCategories,
//        ]);
//    }


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

        if ($form->isSubmitted() && $form->isValid()) {
            $orderBuilder->addItem($product, $form->get('quantity')->getData());
            $deliveryDate = $form->get('deliveryDate')->get('deliveryDate')->getData();
            $orderBuilder->setDeliveryDate($deliveryDate ? $deliveryDate : null, null);

            $clientDetails = new ClientDetails($request->getClientIp(), $request->headers->get('user-agent'), $request->headers->get('accept-language'));
            $orderBuilder->setClientDetails($clientDetails);

            return $this->redirectToRoute('site-checkout-step0-pickExtraGift');
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
    public function addGiftItem(Request $request, Product $product) //, $id = null
    {
        $orderBuilder = $this->orderBuilder;
        try {
            $orderBuilder->addItem($product, 1);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            if ($request->isXmlHttpRequest() && $error) {
                $json = json_encode($error, JSON_UNESCAPED_UNICODE);
                return new JsonResponse($json,400, [], true);
            }
        }
        $clientDetails = new ClientDetails($request->getClientIp(), $request->headers->get('user-agent'), $request->headers->get('accept-language'));
        $orderBuilder->setClientDetails($clientDetails);

        return $this->render('webshop/cart/cart.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'showQuantity' => true,
            'showRemove' => true,
            'showSummary' => true,
        ]);
    }

    /**
     * Removes an item from the cart. Used in JS.
     *
     * @Route("/cart/removeItemFromCart/{id}/{showQuantity}", name="cart-removeItem")
     */
    public function removeItemFromCart(Request $request, $id = null, bool $showQuantity = false): Response //OrderItem $item,
    {
        $orderBuilder = $this->orderBuilder;
        $item = $this->getDoctrine()->getRepository(OrderItem::class)->find($id);
        if ($item) {
            $orderBuilder->removeItem($item);
        }
        if ($request->isXmlHttpRequest()) {
            return $this->render('webshop/cart/cart.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'showQuantity' => true,
                'showRemove' => true,
                'showSummary' => true,
            ]);
        }
        return $this->render('webshop/cart/cart.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'showQuantity' => true,
            'showRemove' => true,
            'showSummary' => true,
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
        return $this->render('webshop/cart/cart-item-quantity.html.twig', [
            'quantityForm' => $form->createView()
        ]);
    }

    /**
     * Updates the quantity value to the Item in the current Order.
     *
     * @Route("/cart/setItemQuantity/{id}", name="cart-setItemQuantity", methods={"POST"})
     */
    public function setItemQuantity(Request $request, OrderItem $item, TranslatorInterface $translator): Response
    {
        $orderBuilder = $this->orderBuilder;
        $quantityBeforeSubmit = $item->getQuantity();
        $form = $this->createForm(SetItemQuantityType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $orderBuilder->setItemQuantity($item, $form->getData()->getQuantity());
            } catch (\Exception $e) {
                $form->get('quantity')->addError(new FormError($e->getMessage()));
            }

            // If AJAX request, renders and returns an HTML form with the value
            if ($form->isValid() && $request->isXmlHttpRequest()) {
                return $this->render('webshop/cart/cart-item-quantity.html.twig', [
                    'quantityForm' => $form->createView(),
                ]);
            }
        }


        /**
         * If AJAX, renders a new form, with the original, pre-submit data, then renders a Response with 400 error code.
         */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {

            // change quantity back to pre-submit value
            $item->setQuantity($quantityBeforeSubmit);
            // create new form
            $form = $this->createForm(SetItemQuantityType::class, $item);
            $html = $this->renderView('webshop/cart/cart-item-quantity.html.twig', [
                'quantityForm' => $form->createView()
            ]);
            return new Response($html,400);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/cart-item-quantity.html.twig', [
            'quantityForm' => $form->createView(),
        ]);
    }

//    /**
//     * Gets the cart. To be used in AJAX calls.
//     *
//     * @Route("/cart/getCart", name="cart-getCart", methods={"GET"})
//     */
//    public function getCart(Request $request): Response
//    {
//        $orderBuilder = $this->orderBuilder;
//        return $this->render('webshop/cart/cart.html.twig', [
//            'order' => $orderBuilder->getCurrentOrder(),
//            'showQuantity' => true,
//            'showRemove' => true,
//            'showSummary' => true,
//        ]);
//    }

    /**
     * NO LONGER USED!!!!
     * It was used when there was no CustomerBasic form in Step1
     */
    public function createMessageForm(): Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(GreetingCardFormType::class, $orderBuilder->getCurrentOrder());

        return $this->render('webshop/cart/greeting-card-form.html.twig', [
            'messageForm' => $form->createView(),
            'order' => $orderBuilder->getId()
        ]);
    }

    /**
     * Saves the data entered in Card message fields
     *
     * @Route("/cart/setMessage", name="cart-setMessage", methods={"POST"})
     */
    public function setMessage(Request $request) //: Response
    {
        $orderBuilder = $this->orderBuilder;
        $form = $this->createForm(GreetingCardFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $orderBuilder->setMessage($data);
        }

        /** Renders form with errors */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/greeting-card-form.html.twig', [
                'greetingCardForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }

        return $this->render('webshop/cart/greeting-card-form.html.twig', [
            'greetingCardForm' => $form->createView(),
        ]);
    }



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