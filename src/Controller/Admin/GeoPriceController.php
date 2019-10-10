<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_DELIVERY_SETTINGS")
 * @Route("/admin")
 */
class GeoPriceController extends BaseController
{
    /**
     * @Route("/geoplace/price/", name="geo-price")
     */
    public function renderGeoPricePage(Request $request)
    {
        return $this->render('admin/geo-price-edit.html.twig', [
            'title' => 'Rendelj szállítási díjat a településhez',
    
        ]);
    }
 
}