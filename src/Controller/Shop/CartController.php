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
use Symfony\Component\HttpFoundation\Session\Session;
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
            try {
                $orderBuilder->addItem($product, $form->get('quantity')->getData());
            } catch (\Exception $e) {
                $form->get('quantity')->addError(new FormError($e->getMessage()));
                $form->addError(new FormError($e->getMessage()));
            }

            $deliveryDate = $form->get('deliveryDate')->get('deliveryDate')->getData();
            $orderBuilder->setDeliveryDate($deliveryDate ? $deliveryDate : null, null);

            $clientDetails = new ClientDetails($request->getClientIp(), $request->headers->get('user-agent'), $request->headers->get('accept-language'));
            $orderBuilder->setClientDetails($clientDetails);
        }

        // Renders form with errors
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/site/product-show-addToCartForm-widget.html.twig', [
                'product' => $product,
                'form' => $form->createView(),
            ]);
            return new Response($html, Response::HTTP_BAD_REQUEST); //400
        }

        if ($request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/site/product-show-addToCartForm-widget.html.twig', [
                'product' => $product,
                'form' => $form->createView(),
            ]);
            return new Response($html);
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
                $html = $this->renderView('webshop/cart/cart.html.twig', [
                    'order' => $orderBuilder->getCurrentOrder(),
                    'showQuantity' => true,
                    'showRemove' => true,
                    'showSummary' => true,
                ]);
                return new Response($html, 200);
            }
        }

        /**
         * If AJAX, renders a new form, with the original, pre-submit data, then renders a Response with 400 error code.
         */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {

            // change quantity back to pre-submit value
            $item->setQuantity($quantityBeforeSubmit);
            $html = $this->renderView('webshop/cart/cart.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'showQuantity' => true,
                'showRemove' => true,
                'showSummary' => true,
            ]);
            return new Response($html,400);
        }

//        /**
//         * Renders form initially with data
//         */
//        return $this->render('webshop/cart/cart-item-quantity.html.twig', [
//            'quantityForm' => $form->createView(),
//        ]);
    }

    /**
     * Gets the number of items in the Cart/Order.
     * Returns a JSON response.
     *
     * @Route("/cart/getItemsCount", name="cart-getItemsCount", methods={"GET"})
     */
    public function getItemsCount(Request $request, Session $session): JsonResponse
    {
        $orderBuilder = $this->orderBuilder;

        if ($request->isXmlHttpRequest()) {
            if ($session->get('orderId') != null && $orderBuilder->getCurrentOrder()->getId() == $session->get('orderId')) {
                $json = json_encode($orderBuilder->getCurrentOrder()->itemsCount(), JSON_UNESCAPED_UNICODE);
                return new JsonResponse($json,200, [], true);
            }
        }
//        return new JsonResponse('error', 400);
    }

    /**
     * Gets the cart. To be used in AJAX calls.
     *
     * @Route("/cart/getCart", name="cart-getCart", methods={"GET"})
     */
    public function getCart(Request $request, Session $session): Response
    {
        $orderBuilder = $this->orderBuilder;

        if ($request->isXmlHttpRequest()) {
            if ($session->get('orderId') != null && $orderBuilder->getCurrentOrder()->getId() == $session->get('orderId')) {
                return $this->render('webshop/cart/cart.html.twig', [
                    'order' => $orderBuilder->getCurrentOrder(),
                    'showQuantity'=> false,
                    'showRemove'=> false,
                    'showTotal'=> true,
                ]);
            }
        }
    }

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