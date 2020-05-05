<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Geo\GeoPrice;
use App\Entity\Geo\GeoPlace;
use App\Entity\Price;
use App\Entity\VatRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/admin")
 */
class GeoPriceApiController extends BaseController
{
    
    private $vatRate;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->vatRate = $em->getRepository(VatRate::class)->find(1);
    }

    
    /**
     * getCitiesBy - returns JSON with one or more Cities
     *
     * Input: nothing               >>> Output: all cities
     * Input: ?zip=2610             >>> Output: cities with this Zip code
     * Input: ?province=Nógrád      >>> Output: cities in this Province
     *
     * Example: /admin/api/geoplace/cities/
     * Example: /admin/api/geoplace/cities/?zip=2610
     * Example: /admin/api/geoplace/cities/?province=Nógrád
     *
     * @Route("/api/geoplace/cities/", name="api-geo-getCitiesBy", methods={"GET"})
     */
    public function apiGetCitiesBy(Request $request)
    {
        $query = $request->query;
        $cities = null;
        if ($query->count() > 0) {
            $zip = $request->query->get('zip');
            $province = $request->query->get('province');
    
            if ($zip !== null ) {
                $cities = $this->getDoctrine()->getRepository(GeoPlace::class)
                    ->findBy(['zip' => $zip]);
            }
            if ($province !== null ) {
                if ($province == 'Budapest') {
                    $orderBy = ['zip' => 'ASC'];
                } else {
                    $orderBy = ['city' => 'ASC'];
                }
                $cities = $this->getDoctrine()->getRepository(GeoPlace::class)
                    ->findBy(['province' => $province], $orderBy);
            }
        } else {
            $cities = $this->getDoctrine()->getRepository(GeoPlace::class)
                ->findAll();
        }
        
        if ($cities) {
            return $this->jsonObjNormalized(['cities' => $cities], 200, ['groups' => 'geoPriceList']);
        } else {
            $errors['message'] = sprintf('Invalid query parameter or value in query: [ /?%s ]',$request->getQueryString());
            return $this->jsonNormalized(['errors' => [$errors],], 422);
        }
    }
    
    /**
     * Example: /admin/api/geoplace/cities/7748
     *
     * @Route("/api/geoplace/cities/{id}", name="api-geo-getCity", methods={"GET"})
     */
    public function apiGetCity(Request $request)
    {
        $id = $request->attributes->get('id');
        $city = $this->getDoctrine()->getRepository(GeoPlace::class)->find($id);
        if ($city) {
            return $this->jsonObjNormalized(['cities' => $city], 200, ['groups' => 'geoPriceList']);
        } else {
            $errors['message'] = sprintf('Nem talált ilyen települést: geoPlaceId=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * Example: /admin/api/geoplace/provinces/
     *
     * @Route("/api/geoplace/provinces/", name="api-geo-getProvinces", methods={"GET"})
     */
    public function apiGetProvinces(Request $request)
    {
        $provinces = $this->getDoctrine()->getRepository(GeoPlace::class)->findAllProvinces();
        if ($provinces) {
            return $this->jsonObjNormalized(['provinces' => $provinces], 200, ['groups' => 'geoPriceList']);
        } else {
            $errors['message'] = sprintf('Nem talált megyéket');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * Input: JSON object: { cities: [], price: number }, the array holds City Ids.
     *
     * {
     *      "cities":[
     *          {7748,''},{7754,''},{7769,''}
     *       ],
     *      "price":"12"
     * }
     * @Route("/api/geoplace/price/", name="api-geo-price-create")
     */
    public function apiCreateGeoPrice(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true);
        
        if (null !== $data && count($data) == 2 && array_key_exists('cities',$data) && array_key_exists('price',$data)) {
            $cities = $data['cities'];
            $priceValue = $data['price'];
            
            $geoPriceList = [];
            foreach ($cities as $c) {
                $city = $this->getDoctrine()->getRepository(GeoPlace::class)->find($c['id']);
                $geoPrice = $this->getDoctrine()->getRepository(GeoPrice::class)->findOneBy(['city' => $city->getId()]);
                if ($geoPrice) {
                    $geoPrice->getPrice()->setNumericValue($priceValue);
                } else {
                    $geoPrice = new GeoPrice();
                    $geoPrice->setCity($city);
                    $price = new Price();
                    $price->setNumericValue($priceValue);
                    $price->setVatRate($this->vatRate);
                    
                    $geoPrice->setPrice($price);
                    
                    $violations = $validator->validate($geoPrice);
                    if ($violations->count() > 0) {
                        $errors = [];
                        foreach ($violations as $violation) {
                            $errors[$violation->getPropertyPath()] = $violation->getMessage();
                        }
                        return $this->jsonNormalized(['errors' => [$errors]], 422);
                    }
                }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($geoPrice);
                $entityManager->flush();
                $geoPriceList[] = $geoPrice;
            }
        } else {
            $errors['message'] = 'Invalid request/form data was received: missing \'cities\' and/or \'price\'';
            return $this->jsonNormalized(['errors' => [$errors],], 422);
        }
        return $this->jsonObjNormalized(['geoPrices' => $geoPriceList], 200, ['groups' => 'geoPriceList']);
    }
    
    /**
     * getGeoPrices - returns JSON with one or more Cities
     *
     * Example: /admin/api/geoplace/prices/
     *
     * @Route("/api/geoplace/prices/", name="api-geo-getGeoPrices", methods={"GET"})
     */
    public function apiGetGeoPrices(Request $request)
    {
        $cities = $this->getDoctrine()->getRepository(GeoPrice::class)
            ->findAll();
        
        if (!$cities) {
            $errors['message'] = 'Nincs még szállítási díj településhez rendelve.';
            return $this->json(['errors' => [$errors],], 422);
        } else {
//            dd($this->json(['cities' => $cities], 200));
            return $this->jsonObjNormalized(['cities' => $cities], 200, ['groups' => 'geoPriceList']);
        }
    }
    
    /**
     * @Route("/api/geoplace/prices/{id}", name="api-geo-getGeoPrice", methods={"GET"})
     */
    public function apiGetGeoPrice(Request $request) //, GeoPrice $geoPrice
    {
        $id = $request->attributes->get('id');
        $geoPrice = $this->getDoctrine()->getRepository(GeoPrice::class)->find($id);
        if ($geoPrice) {
            return $this->jsonNormalized($geoPrice);
        } else {
            $errors['message'] = sprintf('Ennek a településnek még nincs szállítási díja: geoPriceId=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * @Route("/api/geoplace/prices/{id}", name="api-geo-deleteGeoPrice", methods={"DELETE"})
     */
    public function apiDeleteGeoPrice(Request $request)
    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->attributes->get('id');
        $geoPrice = $this->getDoctrine()->getRepository(GeoPrice::class)->find($id);
        if ($geoPrice) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($geoPrice);
            $em->flush();
            return new Response(null, 204);
        } else {
            $errors['message'] = sprintf('Nem törölhető, mert ennek a településnek még nincs szállítási díja: geoPriceId=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
}