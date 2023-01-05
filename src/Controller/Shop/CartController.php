<?php

namespace App\Controller\Shop;

use App\Entity\CartItem;
use App\Entity\GreetingCardMessageCategory;
use App\Entity\Product\ProductStatus;
use App\Model\AddToCartModel;
use App\Model\CartGreetingCard;
use App\Services\CartBuilder;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Services\StoreSessionStorage;
use App\Services\StoreSettings;
use App\Form\AddToCart\CartAddItemType;
use App\Form\GreetingCard\GreetingCardFormType;
use App\Form\Cart\SetItemQuantityType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CartController extends AbstractController
{
    private $cartBuilder;
    private $em;
    private $translator;

    public function __construct(CartBuilder $cartBuilder, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->cartBuilder = $cartBuilder;
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @Route("/cart", name="site-checkout-step0-pickExtraGift")
     */
    public function showCart(StoreSettings $settings, CartBuilder $cartBuilder)
    {
        $user = $this->getUser();

        $giftCategory = $this->em->getRepository(ProductCategory::class)->find($settings->get('general.giftCategory'));
        $gifts = $giftCategory->getProducts();
        $products= $this->em->getRepository(Product::class)->findBy(
            ['status' => $this->em->getRepository(ProductStatus::class)->findOneBy(['shortcode' => ProductStatus::STATUS_ENABLED])],
            ['rank' => 'ASC'],
            8
        );

        $greetingCard = new CartGreetingCard($cartBuilder->getCurrent()->getMessage(), $cartBuilder->getCurrent()->getMessageAuthor());
        $greetingCardForm = $this->createForm(GreetingCardFormType::class, $greetingCard);
        $cardCategories = $this->em->getRepository(GreetingCardMessageCategory::class)
            ->findAll();

        return $this->render('webshop/cart/checkout-step0-cart.html.twig', [
            'cart' => $cartBuilder->getCurrent(),
//            'orderId' => $cartBuilder->getCurrent()->getId(),
//            'progressBar' => 'pickExtraGift',
            'gifts' => $gifts,
            'giftCategory' => $giftCategory,
            'upsellProducts' => $products,
            'greetingCardForm' => $greetingCardForm->createView(),
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
     * @Route("/cart/addItem", name="cart-addItem", methods={"POST"})
     */
    public function addItem(Request $request, AddToCartModel $addToCartModel): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/addItem/{id}');
        }

        $cartBuilder = $this->cartBuilder;
        $id = $request->request->get('cart_add_item')['productId'];
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        $form = $this->createForm(CartAddItemType::class, $addToCartModel, ['product'=>$product]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AddToCartModel $data */
            $data = $form->getData();
            try {
                $cartBuilder->addItem($product, $data->getQuantity());
            } catch (Exception $e) {
                $form->get('quantity')->addError(new FormError($e->getMessage()));
                $form->addError(new FormError($e->getMessage()));
            }
        }

        // Renders form with errors
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/site/product-show-addToCartForm-widget.html.twig', [
                'product' => $product,
                'form' => $form->createView(),
            ]);
            return new Response($html, Response::HTTP_BAD_REQUEST); //400
        }

        $html = $this->renderView('webshop/site/product-show-addToCartForm-widget.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
        return new Response($html);
    }
    
    /**
     * Used on the Checkout Step1 page to add a gift product as an Item to the Order.
     *
     * @Route("/cart/addGift/{id}", name="cart-addGift", methods={"POST"})
     */
    public function addGiftItem(Request $request, Product $product) //, $id = null
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/addGift/{id}');
        }

        $cartBuilder = $this->cartBuilder;
        try {
            $cartBuilder->addItem($product, 1);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if ($error) {
                $json = json_encode($error, JSON_UNESCAPED_UNICODE);
                return new JsonResponse($json,400, [], true);
            }
        }

//        $clientDetails = new ClientDetails($request->getClientIp(), $request->headers->get('user-agent'), $request->headers->get('accept-language'));
//        $cartBuilder->setClientDetails($clientDetails);

        return $this->render('webshop/cart/cart.html.twig', [
            'order' => $cartBuilder->getCurrent(),
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
    public function removeItem(Request $request, $id = null, bool $showQuantity = false): Response //OrderItem $item,
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/removeItemFromCart/{id}/{showQuantity}');
        }

        $cartBuilder = $this->cartBuilder;
        $item = $this->getDoctrine()->getRepository(CartItem::class)->find($id);
        if ($item) {
            $cartBuilder->removeItem($item);
        }

        return $this->render('webshop/cart/cart.html.twig', [
            'order' => $cartBuilder->getCurrent(),
            'showQuantity' => true,
            'showRemove' => true,
            'showSummary' => true,
        ]);
    }

    /**
     * Creates the dropdown form in the cart, used to change quantity.
     * It is used within the template with 'generate'.
     * Appears in the Cart page and the sidebar cart too.
     */
    public function setItemQuantityForm(CartItem $item): Response
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
    public function setItemQuantity(Request $request, CartItem $item, TranslatorInterface $translator): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setItemQuantity/{id}');
        }

        $cartBuilder = $this->cartBuilder;
        $quantityBeforeSubmit = $item->getQuantity();
        $form = $this->createForm(SetItemQuantityType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $cartBuilder->setItemQuantity($item, $form->getData()->getQuantity());
            } catch (Exception $e) {
                $form->get('quantity')->addError(new FormError($e->getMessage()));
            }

            if ($form->isValid()) {
                $html = $this->renderView('webshop/cart/cart.html.twig', [
                    'order' => $cartBuilder->getCurrent(),
                    'showQuantity' => true,
                    'showRemove' => true,
                    'showSummary' => true,
                ]);
                return new Response($html, 200);
            }
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            // change quantity back to pre-submit value
            $item->setQuantity($quantityBeforeSubmit);
            $html = $this->renderView('webshop/cart/cart.html.twig', [
                'order' => $cartBuilder->getCurrent(),
                'showQuantity' => true,
                'showRemove' => true,
                'showSummary' => true,
            ]);
            return new Response($html,400);
        }
        return new JsonResponse($this->translator->trans('error.ajax-no-data-was-returned'), 400);
    }

//    /**
//     * Gets the number of items in the Cart/Order.
//     * Returns a JSON response.
//     *
//     * @Route("/cart/getItemsCount", name="cart-getItemsCount", methods={"GET"})
//     */
//    public function getItemsCount(Request $request, Session $session): JsonResponse
//    {
//        if (!$request->isXmlHttpRequest()) {
//            throw $this->createNotFoundException('HIBA: /cart/getItemsCount');
//        }
//
//        $cartIdInSession = $session->get(StoreSessionStorage::CART_ID);
//        if ($cartIdInSession == null && $this->cartBuilder->getCurrent()->getId() == $cartIdInSession) {
//            $json = json_encode($this->cartBuilder->getCurrent()->getItemCount(), JSON_UNESCAPED_UNICODE);
//            return new JsonResponse($json,200, [], true);
//        }
//        return new JsonResponse($this->translator->trans('error.ajax-no-data-was-returned'), 400);
//    }

    /**
     * Gets the cart. To be used in AJAX calls.
     *
     * @Route("/cart/getCart", name="cart-getCart", methods={"GET"})
     */
    public function getCart(Request $request, Session $session): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/getCart');
        }

        $cartIdInSession = $session->get(StoreSessionStorage::CART_ID);
        if ($cartIdInSession != null && $this->cartBuilder->getCurrent()->getId() == $cartIdInSession) {
            return $this->render('webshop/cart/cart.html.twig', [
                'order' => $this->cartBuilder->getCurrent(),
                'showQuantity'=> false,
                'showRemove'=> false,
                'showTotal'=> true,
            ]);
        }

        $json = json_encode(['error' => 'Valami hiba történt.'], JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json,400, [], true);
    }

    /**
     * Saves the data entered in Card message fields
     *
     * @Route("/cart/setMessage", name="cart-setMessage", methods={"POST"})
     */
    public function setMessage(Request $request) //: Response
    {
        $form = $this->createForm(GreetingCardFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->cartBuilder->setMessage($data);
        }

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
}