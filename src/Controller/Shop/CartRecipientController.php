<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Geo\GeoCountry;
use App\Entity\Geo\GeoPlace;
use App\Services\OrderBuilder;
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

// !!! NOT IN USE !!!!
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
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /search');
        }

        $geoPlaces = $geoRep->findAllMatching($request->query->get('query'));

        return $this->json($geoPlaces,200, [], [
            'groups' => ['main'],
        ]);

    }

//    /**
//     * Handles the Sender form. Only available for logged in users.
//     * Create and submit the form from AJAX.
//     *
//     * @Route("/cart/editRecipient/{id}", name="cart-editRecipient", methods={"POST"})
//     */
//    public function editRecipientForm(Request $request, ?Recipient $recipient, $id = null, ValidatorInterface $validator)
//    {
//        if (!$request->isXmlHttpRequest()) {
//            throw $this->createNotFoundException('HIBA: /cart/editRecipient/{id}');
//        }
//
//        // If User from session is equal to User in Recipient
//        $orderBuilder = $this->orderBuilder;
//        $customer = $orderBuilder->getCurrentOrder()->getCustomer();
//        if (!$recipient) {
//            $orderBuilder->removeRecipient($orderBuilder->getCurrentOrder()->getRecipient());  // torli a mar elmentett Recipientet
//
//            $recipient = new Recipient();
//            $recipient->setCustomer($orderBuilder->getCurrentOrder()->getCustomer());
//            // Ezzel mondom meg neki, mi legyen a default country ertek (azaz Magyarorszag)
//            $address = new Address();
//            $address->setCountry($this->getDoctrine()->getRepository(GeoCountry::class)->findOneBy(['alpha2' => 'hu']));
//            $recipient->setAddress($address);
//            $form = $this->createForm(RecipientType::class, $recipient);
//        } else {
//            $form = $this->createForm(RecipientType::class, $recipient);
//        }
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            //elobb elmentem a recipient formadatokat a Recipient tablaba
//            $recipient = $form->getData();
//
//            $phone = $form->get('phone')->getData();
//
//            if ($customer) {
//                $recipient->setCustomer($customer); // a cimzettet egy Customerhez kotjuk
//            }
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($recipient);
//            $entityManager->flush();
//
//            $orderBuilder->setRecipient($recipient);
//        }
//        // Renders form with errors
//        if ($form->isSubmitted() && !$form->isValid()) {
//            $html = $this->renderView('webshop/cart/recipient_form.html.twig', [
//                'recipientForm' => $form->createView(),
//            ]);
//            return new Response($html, 400);
//        }
//
//        return $this->render('webshop/cart/recipient_form.html.twig', [
//            'recipientForm' => $form->createView(),
//        ]);
//    }



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
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /cart/getPlaceByZip/{zip}');
        }

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


}