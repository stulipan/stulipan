<?php

namespace App\Controller\Shop;

use App\Entity\Geo\GeoPlace;
use App\Entity\OrderBuilder;
use App\Entity\Product\ProductCategory;
use App\Repository\GeoPlaceRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CartUtilsController extends AbstractController
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
     * Example: stulipan.dfr/search/?query=Debrecen
     *
     * @Route("/search/", name="cart-search-api", methods={"GET"})
     */
    public function searchPlacesApi(GeoPlaceRepository $geoRep, Request $request)
    {
//        $geoPlace = $geoRep->findAllOrdered();
//        dd($request->query->get('query'));
        $geoPlaces = $geoRep->findAllMatching($request->query->get('query'));

        return $this->json($geoPlaces,200, [], [
            'groups' => ['main'],
        ]);

    }
    
    /**
     * @Route("/geoApi/", name="api-geo-listByProvince", methods={"GET"})
     */
    public function apiListCitiesByProvince(Request $request)
    {
        $province = 'Pest';
        $cities = $this->getDoctrine()->getRepository(GeoPlace::class)
            ->findBy(['province' => $province]);
    
//        return $this->serializeIntoJsonResponse(['cities' => $cities],200,[
//            'groups' => ['main'],
//        ]);
        // Megegyezik a fenti return megoldással
        return $this->json(['cities' => $cities],200, [], [
            'groups' => ['main'],
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
     * Renders the dropdown cart. The items are retrieved from session
     */
    public function cartDetailsDropdown()
    {
        $orderBuilder = $this->orderBuilder;
        return $this->render('webshop/site/navbar-cart-dropdown.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
        ]);
    }

    /**
     * Renders the slider cart. The items are retrieved from session
     */
    public function showSidebarCart()
    {
        $orderBuilder = $this->orderBuilder;
        return $this->render('webshop/site/navbar-cart-sidebar.html.twig', [
            'order' => $orderBuilder->getCurrentOrder(),
            'totalAmountToPay' => $orderBuilder->summary()->getTotalAmountToPay(),
        ]);
    }

    /**
     * Renders the menu dropdown which lists all categories.
     */
    public function listProductCategoriesInMenu()
    {
        $categories = $this->getDoctrine()->getRepository(ProductCategory::class)
            ->findBy(['enabled' => 1]);

        return $this->render('webshop/site/navbar-menu-categoriesDropdown.html.twig', [
            'categories' => $categories,
        ]);
    }

}