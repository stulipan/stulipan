<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Geo\GeoCountry;
use App\Entity\Model\CustomerBasic;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\PaymentMethod;
use App\Entity\Recipient;
use App\Entity\Sender;
use App\Entity\ShippingMethod;
use App\Form\Checkout\AcceptTermsType;
use App\Form\Checkout\PaymentMethodType;
use App\Form\Checkout\SameAsRecipientType;
use App\Form\Checkout\ShippingMethodType;
use App\Form\CustomerBasic\CustomerBasicType;
use App\Form\DeliveryDate\CartHiddenDeliveryDateFormType;
use App\Form\RecipientType;
use App\Form\SenderType;
use App\Model\CheckoutPaymentMethod;
use App\Model\CheckoutShippingMethod;
use App\Services\CheckoutBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutSnippetsController extends AbstractController
{
    private $checkoutBuilder;
    private $em;
    private $session;

    public function __construct(CheckoutBuilder $checkoutBuilder, EntityManagerInterface $em, SessionInterface $session)
    {
        $this->checkoutBuilder = $checkoutBuilder;
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * Handles the CustomerBasic form. Via AJAX.
     *
     * @Route("/cart/setCustomer/{id}", name="cart-setCustomer", methods={"POST", "GET"})
     */
    public function setCustomer(Request $request, ?CustomerBasic $customerBasic, $id = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setCustomer/{id}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $form = $this->createForm(CustomerBasicType::class, $customerBasic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CustomerBasic $data */
            $data = $form->getData();
            $checkoutBuilder->setCustomerBasic($data);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/customerBasic-form.html.twig', [
                'customerForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }

        return $this->render('webshop/cart/customerBasic-form.html.twig', [
            'customerForm' => $form->createView(),
        ]);
    }

    /**
     * Handles the Sender form. Only available for logged in users.
     * Create and submit the form from AJAX.
     *
     * @Route("/cart/editRecipient/{id}", name="cart-editRecipient", methods={"POST"})
     */
    public function editRecipientForm(Request $request, ?Recipient $recipient, $id = null, ValidatorInterface $validator)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/editRecipient/{id}');
        }

        // If User from session is equal to User in Recipient
        $checkoutBuilder = $this->checkoutBuilder;
        if (!$recipient) {
            $recipient = new Recipient();
            if ($this->getUser()) {
                $recipient->setUser($this->getUser());
            }
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
            $form = $this->createForm(RecipientType::class, $recipient);
        } else {
            $form = $this->createForm(RecipientType::class, $recipient);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipient = $form->getData();
            $checkoutBuilder->setRecipient($recipient, $this->isGranted('ROLE_USER'), false);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/recipient_form.html.twig', [
                'recipientForm' => $form->createView(),
            ]);
            return new Response($html, 400);
        }
        return $this->render('webshop/cart/recipient_form.html.twig', [
            'recipientForm' => $form->createView(),
        ]);
    }

    /**
     * Gets the list of Recipients. Handles 2 situations:
     *
     * 1. A Customer is assigned to the current order (User is logged in)
     * Returns all the customer's recipients.
     *
     * 2. No customer was found in the current order (User isn't logged in, eg: Guest checkout)
     * In this case returns only one Recipient.
     *
     * @Route("/cart/getRecipients", name="cart-getRecipients", methods={"GET"})
     */
    public function getRecipients(Request $request)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/getRecipients');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        if ($this->getUser()) {
            $recipients = $this->getUser()->getRecipients();
        }
//        if ($checkoutBuilder->getCurrent()->getCustomer()) {
//            $recipients = $checkoutBuilder->getCurrent()->getCustomer()->getRecipients();
//        }
        else {
            $recipients = new ArrayCollection();
            if ($checkoutBuilder->hasRecipient()) {
                $recipients->add($checkoutBuilder->getCurrent()->getRecipient());
            }
        }
        return $this->render('webshop/cart/recipient_list.html.twig', [
            'recipients' => $recipients,
            'selectedRecipient' => $checkoutBuilder->getCurrent()->getRecipient() ? $checkoutBuilder->getCurrent()->getRecipient()->getId() : null,
        ]);
    }

    /**
     * @Route("/cart/getRecipient", name="cart-getRecipient", methods={"GET"})
     */
    public function getRecipient(Request $request)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/getRecipient');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        if ($checkoutBuilder->hasRecipient()) {
            $recipient = $checkoutBuilder->getCurrent()->getRecipient();
        } else {
            $recipient = new Recipient();
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
        }
        $form = $this->createForm(RecipientType::class, $recipient);
        return $this->render('webshop/cart/recipient_form.html.twig', [
            'recipientForm' => $form->createView(),
        ]);
    }

    /**
     * Picks a Recipient from the recipient list and assigns it to the current Order.
     * It is used in AJAX.
     *
     * @Route("/cart/pickRecipient/{id}", name="cart-pickRecipient", methods={"POST"})
     */
    public function pickRecipient(Request $request, Recipient $recipient)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/pickRecipient/{id}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        // If User from session is equal to User in Recipient
        if ($this->getUser() === $recipient->getUser()) {
//            $checkoutBuilder->setRecipient($recipient);
            $checkoutBuilder->setRecipient($recipient, $this->isGranted('ROLE_USER'), false);

            return $this->render('webshop/cart/recipient_form.html.twig', [
                'recipientForm' => $this->createForm(RecipientType::class, $recipient)->createView(),
                'selectedRecipient' => $checkoutBuilder->getCurrent()->getRecipient() ? $checkoutBuilder->getCurrent()->getRecipient()->getId() : null,
            ]);
        }
    }

    /**
     * Deletes a Recipient. Used in AJAX.
     *
     * @Route("/cart/deleteRecipient/{id}", name="cart-deleteRecipient", methods={"DELETE", "GET"})
     */
    public function deleteRecipient(Request $request, ?Recipient $recipient, $id = null)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/deleteRecipient/{id}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        // If User from session is equal to User in Recipient
        if ($this->getUser() === $recipient->getUser()) {
            if ($checkoutBuilder->getCurrent()->getRecipient() === $recipient) {
                $checkoutBuilder->removeRecipient();
            }
            $recipient->setUser(null);
            $this->em->remove($recipient);
            $this->em->flush();
            return $this->redirectToRoute('cart-getRecipient');
        }
    }

    /**
     * @Route("/cart/setShippingMethod", name="cart-setShippingMethod", methods={"POST"})
     */
    public function setShippingMethod(Request $request, CheckoutShippingMethod $shippingMethod): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setShippingMethod');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $form = $this->createForm(ShippingMethodType::class, $shippingMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkoutBuilder->setShippingMethod($form->getData()->getShippingMethod());
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/shipping-method-form.html.twig', [
                'shippingMethodForm' => $form->createView(),
                'shippingMethods' => $this->em->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
            ]);
            return new Response($html,400);
        }

        return $this->render('webshop/cart/shipping-method-form.html.twig', [
            'shippingMethodForm' => $form->createView(),
            'shippingMethods' => $this->em->getRepository(ShippingMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
        ]);
    }

    /**
     * @Route("/cart/setDeliveryDate", name="cart-setDeliveryDate", methods={"POST"})
     *
     */
    public function setDeliveryDate(Request $request, HiddenDeliveryDate $date) //: Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setDeliveryDate');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $form = $this->createForm(CartHiddenDeliveryDateFormType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $checkoutBuilder->setDeliveryDate($data->getDeliveryDate(), $data->getDeliveryInterval());
            $checkoutBuilder->setSchedulingPrice($data->getDeliveryFee());

            // it returns Success (code 200)
            return $this->render('webshop/cart/hidden-delivery-date-form.html.twig', [
                'hiddenDateForm' => $form->createView(),
            ]);
        }
        // Renders form with errors
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/hidden-delivery-date-form.html.twig', [
                'hiddenDateForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
    }

    /**
     * @Route("/cart/setPaymentMethod", name="cart-setPaymentMethod", methods={"POST"})
     */
    public function setPaymentMethod(Request $request, CheckoutPaymentMethod $paymentMethod): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setPaymentMethod');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkoutBuilder->setPaymentMethod($form->getData()->getPaymentMethod());
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/payment-method-form.html.twig', [
                'paymentMethodForm' => $form->createView(),
                'paymentMethods' => $this->em->getRepository(PaymentMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
            ]);
            return new Response($html,400);
        }
        return $this->render('webshop/cart/payment-method-form.html.twig', [
            'paymentMethodForm' => $form->createView(),
            'paymentMethods' => $this->em->getRepository(PaymentMethod::class)->findBy(['enabled' => true], ['ordering' => 'ASC']),
        ]);
    }

    /**
     * @Route("/cart/setSameAsRecipient/", name="cart-setSameAsRecipient", methods={"POST"})
     */
    public function setSameAsRecipient(Request $request, ValidatorInterface $validator, ?bool $sameAsRecipient = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setSameAsRecipient');
        }
        $checkoutBuilder = $this->checkoutBuilder;

        $form = $this->createForm(SameAsRecipientType::class, [ 'sameAsRecipient' => $sameAsRecipient]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('sameAsRecipient')->getData();
            $checkoutBuilder->setSender(null, $data, $this->isGranted('ROLE_USER'), false);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/sender-same-as-recipient.html.twig', [
                'sameAsRecipientForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
        $html = $this->renderView('webshop/cart/sender-same-as-recipient.html.twig', [
            'sameAsRecipientForm' => $form->createView(),
        ]);
        return new Response($html,200);

//        $senderInOrder = $checkoutBuilder->getCurrent()->getSender();
//        if (!$senderInOrder) {
////            $this->session->set('sameAsRecipient', true);
//            $recipient = $checkoutBuilder->getCurrent()->getRecipient();
//            $sender = new Sender();
//            $sender->setFirstname($recipient->getFirstname());
//            $sender->setLastname($recipient->getLastname());
//            $sender->setPhone($recipient->getPhone());
//            $senderAddress = new Address();
//            $senderAddress->setAddressTypeToBilling();
//            $senderAddress->setStreet($recipient->getAddress()->getStreet());
//            $senderAddress->setCity($recipient->getAddress()->getCity());
//            $senderAddress->setZip($recipient->getAddress()->getZip());
//            $senderAddress->setProvince($recipient->getAddress()->getProvince());
//            $senderAddress->setCountry($recipient->getAddress()->getCountry());
//            $sender->setAddress($senderAddress);
//            $sender->setCustomer($checkoutBuilder->getCurrent()->getCustomer());
//
//            $errors = $validator->validate($sender);
//            if (count($errors) > 0) {
//                throw $this->createNotFoundException(
//                    'HIBA: setSameAsRecipient failed. The new Sender could not be validated.'
//                );
//            }
//            $this->em->persist($sender);
//            $this->em->flush();
//
//            $checkoutBuilder->setSender($sender);
//        }
//        return new Response('',200);
    }

    /**
     * @Route("/cart/setAcceptTerms/{isAcceptedTerms}", name="cart-setAcceptTerms", methods={"POST"})
     */
    public function setAcceptTerms(Request $request, ?bool $isAcceptedTerms = null): Response  //, ?Order $order
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/setAcceptTerms/{isAcceptedTerms}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $form = $this->createForm(AcceptTermsType::class, [ 'isAcceptedTerms' => $isAcceptedTerms]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkoutBuilder->setIsAcceptedTerms($form->get('isAcceptedTerms')->getData());
        }
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/accept-terms-form.html.twig', [
                'acceptTermsForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
        $html = $this->renderView('webshop/cart/accept-terms-form.html.twig', [
            'acceptTermsForm' => $form->createView(),
        ]);
        return new Response($html,200);
    }





    /**
     * Handles the Sender form. It is used to create and submit the form from JS.
     *
     * @Route("/cart/editSender/{id}", name="cart-editSender", methods={"POST"})
     */
    public function editSenderForm(Request $request, ?Sender $sender, $id = null, ValidatorInterface $validator)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/editSender/{id}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        $customer = $checkoutBuilder->getCurrent()->getCustomer();
        if (!$sender) {
            $sender = new Sender();
            $sender->setUser($this->getUser());
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
            $form = $this->createForm(SenderType::class, $sender);
        } else {
            $form = $this->createForm(SenderType::class, $sender);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sender = $form->getData();
//            $checkoutBuilder->setSender($sender);
//            $checkoutBuilder->setSameAsShipping(false);
            $checkoutBuilder->setSender($sender, false, $this->isGranted('ROLE_USER'), false);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $html = $this->renderView('webshop/cart/sender_form.html.twig', [
                'senderForm' => $form->createView(),
            ]);
            return new Response($html, 400);
        }

        return $this->render('webshop/cart/sender_form.html.twig', [
            'senderForm' => $form->createView(),
        ]);
    }

    /**
     * Gets the list of Senders. Handles 2 situations, see getRecipients()
     *
     * @Route("/cart/getSenders", name="cart-getSenders")
     */
    public function getSenders(Request $request)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/getSenders');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        if ($this->getUser()) {
            $senders = $this->getUser()->getSenders();
        }
//        if ($checkoutBuilder->getCurrent()->getCustomer()) {
//            $senders = $checkoutBuilder->getCurrent()->getCustomer()->getSenders();
//        }
        else {
            $senders = new ArrayCollection();
            if ($checkoutBuilder->hasSender()) {
                $senders->add($checkoutBuilder->getCurrent()->getSender());
            }
        }

        return $this->render('webshop/cart/sender_list.html.twig', [
            'senders' => $senders,
            'selectedSender' => $checkoutBuilder->getCurrent()->getSender() ? $checkoutBuilder->getCurrent()->getSender()->getId() : null,
        ]);
    }

    /**
     * @Route("/cart/getSender", name="cart-getSender")
     */
    public function getSender(Request $request)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/getSender');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        if ($checkoutBuilder->hasSender()) {
            $sender = $checkoutBuilder->getCurrent()->getSender();
        } else {
            $sender = new Sender();
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->em->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $sender->setAddress($address);
        }

        $form = $this->createForm(SenderType::class, $sender);

        return $this->render('webshop/cart/sender_form.html.twig', [
            'senderForm' => $form->createView(),
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
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/pickSender/{id}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        if ($this->getUser() === $sender->getUser()) {
//            $checkoutBuilder->setSender($sender);
            $checkoutBuilder->setSender($sender, false, $this->isGranted('ROLE_USER'), false);

            return $this->render('webshop/cart/sender_form.html.twig', [
                'senderForm' => $this->createForm(SenderType::class, $sender)->createView(),
                'selectedSender' => $checkoutBuilder->getCurrent()->getSender() ? $checkoutBuilder->getCurrent()->getSender()->getId() : null,
            ]);
        }
    }

    /**
     * Deletes a Sender. Used in JS.
     *
     * @Route("/cart/deleteSender/{id}", name="cart-deleteSender", methods={"DELETE", "GET"})
     */
    public function deleteSender(Request $request, ?Sender $sender, $id = null)
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/deleteSender/{id}');
        }

        $checkoutBuilder = $this->checkoutBuilder;
        // If User from session is equal to User in Sender
        if ($this->getUser() === $sender->getUser()) {
            if ($checkoutBuilder->getCurrent()->getSender() == $sender) {
                $checkoutBuilder->removeSender();
            }
            $sender->setUser(null);
            $this->em->remove($sender);
            $this->em->flush();
            return $this->redirectToRoute('cart-getSender');
        }
    }
    
    
    
//    /**
//     * Handles the Customer form. Via AJAX.
//     *
//     * @ Route("/cart/setCustomer/{id}", name="cart-setCustomer", methods={"POST", "GET"})
//     */
//    public function setCustomer1(Request $request, ?Customer $customer, $id = null, StoreSettings $storeSettings)
//    {
//        $orderBuilder = $this->orderBuilder;
//        $form = $this->createForm(CustomerType::class, $customer); //,
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            /** @var Customer $data */
//            $data = $form->getData();
//
////            if ($storeSettings->get('general.flower-shop-mode') === true) {
////                $data->setFirstname($orderBuilder->getCurrent()->getRecipient()->getFirstname());
////                $data->setLastname($orderBuilder->getCurrent()->getRecipient()->getLastname());
////                $data->setPhone($orderBuilder->getCurrent()->getRecipient()->getPhone());
////            }
////            dd($data);
//
//            // If object has Id, it's already saved into db, and is not a new object.
//            if (!$data->getId()) {
//                // find a customer with this email address
//                $existingCustomer = $this->getDoctrine()->getRepository(Customer::class)->findOneBy(['email' => $data->getEmail()]);
//            }
//
//            // replace old Customer info with newly provided in the form
//            if (isset($existingCustomer) && $existingCustomer) {
//                $customer = $existingCustomer;
//                $customer->setEmail($data->getEmail());
//                $customer->setPhone($data->getPhone());
//                $customer->setFirstname($data->getFirstname());
//                $customer->setLastname($data->getLastname());
//                $customer->setAcceptsMarketing($data->isAcceptsMarketing());
//            } else {
//                $customer = $data;
//            }
//
//            // Update optin date only when isAcceptsMarketing is true
//            if ($customer->isAcceptsMarketing()) {
//                $customer->setAcceptsMarketingUpdatedAt(new DateTime('now'));
//                $customer->setMarketingOptinLevel(Customer::OPTIN_LEVEL_SINGLE_OPTIN);
//            }
//
//            $customer->addOrder($orderBuilder->getCurrent());
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($customer);
//            $em->flush();
//
//            $orderBuilder->setCustomer($customer);
//        }
//
//        /** Renders the form with errors */
//        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('webshop/cart/customer-form.html.twig', [
//                'customerForm' => $form->createView(),
//            ]);
//            return new Response($html,400);
//        }
//
//        if ($form->isSubmitted() && $form->isValid() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('webshop/cart/customer-form.html.twig', [
//                'customerForm' => $form->createView(),
//            ]);
//            return new Response($html,200);
//        }
//
////        // If not Ajax call, then it was called from the My Account > User Details page.
////        // Therefore we must redirect back to User Details page.
////
////        return $this->redirectToRoute('site-user-myDetails', ['customerForm' => $form->createView()]);
//        /**
//         * Renders form initially with data
//         */
//        return $this->render('webshop/cart/customer-form.html.twig', [
//            'customerForm' => $form->createView(),
//        ]);
//    }

}