<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Geo\GeoCountry;
use App\Entity\Geo\GeoPlace;
use App\Entity\OrderBuilder;
use App\Entity\Recipient;
use App\Repository\GeoPlaceRepository;
use App\Form\RecipientType;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CartRecipientController extends AbstractController
{
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;

    private $errorMessage = 'Unauthorized access: Request must come through XmlHttpRequest and user must be logged in!';

    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * @Route("/search/", name="cart-search-api", methods={"GET"})
     */
    public function searchPlacesApi(GeoPlaceRepository $geoRep, Request $request)
    {
//        $geoPlace = $geoRep->findAllOrdered();
        $geoPlaces = $geoRep->findAllMatching($request->query->get('query'));

        return $this->json($geoPlaces,200, [], [
            'groups' => ['main'],
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
//        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        // If User from session is equal to User in Recipient
        $orderBuilder = $this->orderBuilder;
        $customer = $orderBuilder->getCurrentOrder()->getCustomer();
        if (!$recipient) {
            $orderBuilder->removeRecipient($orderBuilder->getCurrentOrder()->getRecipient());  // torli a mar elmentett Recipientet

            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
            $form = $this->createForm(RecipientType::class, $recipient);
        } else {
            $form = $this->createForm(RecipientType::class, $recipient);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //elobb elmentem a recipient formadatokat a Recipient tablaba
            $recipient = $form->getData();

            $phone = $form->get('phone')->getData();

            if ($customer) {
                $recipient->setCustomer($customer); // a cimzettet egy Customerhez kotjuk
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipient);
            $entityManager->flush();

            $orderBuilder->setRecipient($recipient);

            /** If AJAX request, returns the current Recipient */
            if ($request->isXmlHttpRequest()) {
//                    return $this->redirectToRoute('cart-getRecipient');
                return $this->render('webshop/cart/recipient_form.html.twig', [
                    'order' => $orderBuilder->getCurrentOrder(),
                    'recipientForm' => $form->createView(),
                ]);
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && !$form->isValid() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/recipient_form.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'recipientForm' => $form->createView(),
            ]);
            return new Response($html, 400);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/recipient_form.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'recipientForm' => $form->createView(),
        ]);
    }



    /**
     * Gets the City and Province based on the Zip code entered by the user.
     * Returns a JSON response, with a 'success' field set to 'true' if results were found, otherwise returns 'false'.
     *
     * It's used in the Recipient and Sender forms, via JS.
     *
     * @Route("/cart/getPlaceByZip/{zip}", name="cart-getPlaceByZip", methods={"GET"})
     */
    public function getPlaceByZip(Request $request, int $zip = null)
    {
        if (!$zip) {
            $zip = $request->query->get('zip');
        }
        if ($zip) {
            $place = $this->getDoctrine()->getRepository(GeoPlace::class)
                ->findOneBy(['zip' => $zip]);
        }

        try {
            if (null !== $place && '' !== $place) {
                return new JsonResponse([
                    'success' => true,
                    'city' => '' === $place->getDistrict() || null === $place->getDistrict() ? $place->getCity() : $place->getCity().' - '.$place->getDistrict().' kerület',
                    'province' => $place->getProvince(),
                ]);
            } else {
                throw new Exception('GeoPlace does not exist!');
            }
        } catch (\Exception $exception) {
            return new JsonResponse([
                'success' => false,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);
        }
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

        $orderBuilder = $this->orderBuilder;
        /** If the Order has a Customer, returns the list of the customer's Recipients */
        if ($orderBuilder->getCurrentOrder()->getCustomer()) {
            $recipients = $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients();
        } /** Else simply returns the Recipient from within the Order (Checkout whithout user registration) */
        else {
            $recipients = new ArrayCollection();
            /** Verifies if a Recipient exists. If not return the Recipient form. */
            if ($orderBuilder->hasRecipient()) {
                $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
            }
        }
        if (!$recipients || $recipients->isEmpty()) {
            $recipient = new Recipient();
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
            $form = $this->createForm(RecipientType::class, $recipient);

            return $this->render('webshop/cart/recipient_form.html.twig', [
                'order' => $orderBuilder->getCurrentOrder(),
                'recipientForm' => $form->createView(),
            ]);
        }
        return $this->render('webshop/cart/recipient_list.html.twig', [
            'recipients' => $recipients,
            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
        ]);
    }

    /**
     * @Route("/cart/getRecipient", name="cart-getRecipient", methods={"GET"})
     */
    public function getRecipient(Request $request)
    {
        $orderBuilder = $this->orderBuilder;
        if ($orderBuilder->hasRecipient()) {
            $recipient = $orderBuilder->getCurrentOrder()->getRecipient();
        } else {
            $recipient = new Recipient();
            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
            $address = new Address();
            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
            $recipient->setAddress($address);
            $form = $this->createForm(RecipientType::class, $recipient);

            return $this->render('webshop/cart/recipient_form.html.twig', [
//                'order' => $orderBuilder->getCurrentOrder(),
                'recipientForm' => $form->createView(),
            ]);
        }
        return $this->render('webshop/cart/recipient_form.html.twig', [
            'recipientForm' => $this->createForm(RecipientType::class, $recipient)->createView(),
            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
        ]);
//        return $this->render('webshop/cart/recipient-current.html.twig', [
//            'recipient' => $recipient,
//            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
//        ]);
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

        // If User from session is equal to User in Recipient
        if ($this->getUser() === $recipient->getCustomer()) {
            $orderBuilder = $this->orderBuilder;
            $orderBuilder->setRecipient($recipient);

            return $this->render('webshop/cart/recipient_form.html.twig', [
                'recipientForm' => $this->createForm(RecipientType::class, $recipient)->createView(),
                'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
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

        // If User from session is equal to User in Recipient
        if ($this->getUser() === $recipient->getCustomer()) {
            $this->orderBuilder->getCustomer()->removeRecipient($recipient);
            if ($this->orderBuilder->getCurrentOrder()->getRecipient() == $recipient) {
                $this->orderBuilder->removeRecipient();
            }
            //            $this->orderBuilder->setFallbackRecipient();
            $this->getDoctrine()->getManager()->remove($recipient);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('cart-getRecipient');
        }
    }
}