<?php

namespace App\Controller\Shop;

use App\Entity\Geo\GeoPlace;
use App\Entity\OrderBuilder;
use App\Entity\Recipient;
use App\Repository\GeoPlaceRepository;
use App\Form\RecipientType;

use Doctrine\Common\Collections\ArrayCollection;
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
     * Handles the Sender form. It is used to create and submit the form from JS.
     *
     * @Route("/cart/editRecipient/{id}", name="cart-editRecipient")
     */
    public function editRecipientForm(Request $request, ?Recipient $recipient, $id = null, ValidatorInterface $validator)
    {
        $orderBuilder = $this->orderBuilder;
        $customer = $orderBuilder->getCurrentOrder()->getCustomer();
        if (!$recipient) {
            $recipient = new Recipient();
            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
            $form = $this->createForm(RecipientType::class, $recipient);
        } else {
            $form = $this->createForm(RecipientType::class, $recipient);
        }
        $form->handleRequest($request);
//        dd($form->isValid());

        if ($form->isSubmitted() && $form->isValid()) {
            //elobb elmentem a recipient formadatokat a Recipient tablaba
            $recipient = $form->getData();

            $phone = $form->get('phone')->getData();


            if ($orderBuilder->getCurrentOrder()->getCustomer()) {
                $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer()); // a cimzettet egy Customerhez kotjuk
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipient);
            $entityManager->flush();

            $orderBuilder->setRecipient($recipient);

            /**
             * If AJAX request, returns the list of Recipients
             */
            if ($request->isXmlHttpRequest()) {
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

        /**
         * Renders form initially with data
         */
        return $this->render('webshop/cart/recipient_form.html.twig', [
            'order' => $orderBuilder,
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
     * @Route("/cart/getRecipients", name="cart-getRecipients")
     */
    public function getRecipients()
    {
        $orderBuilder = $this->orderBuilder;
        /**
         * If the Order has a Customer, returns the list of the customer's Recipients
         */
        if ($orderBuilder->getCurrentOrder()->getCustomer()) {
            $recipients = $orderBuilder->getCurrentOrder()->getCustomer()->getRecipients();
        }
        /**
         * Else simply returns the Recipient from within the Order (Checkout whithout user registration)
         */
        else {
            $recipients = new ArrayCollection();
            $recipients->add($orderBuilder->getCurrentOrder()->getRecipient());
        }

        return $this->render('webshop/cart/recipient_list.html.twig', [
            'recipients' => $recipients,
            'selectedRecipient' => $orderBuilder->getCurrentOrder()->getRecipient() ? $orderBuilder->getCurrentOrder()->getRecipient()->getId() : null,
        ]);
    }

    /**
     * Picks a Recipient from the recipient list and assigns it to the current Order.
     * It is used in JS.
     *
     * @Route("/cart/pickRecipient/{id}", name="cart-pickRecipient")
     */
    public function pickRecipient(Request $request, Recipient $recipient)
    {
        $orderBuilder = $this->orderBuilder;
        $orderBuilder->setRecipient($recipient);
        $html = $this->render('admin/item.html.twig', [
            'item' => 'Címzett sikeresen kiválasztva!',
        ]);
        return new Response($html, 200);
    }
}