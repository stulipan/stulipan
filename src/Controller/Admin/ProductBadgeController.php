<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductStatus;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class ProductBadgeController extends BaseController
{
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                           Product Statuses API                                 ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Route("/api/product/badges", name="api-product-getBadges", methods={"GET"})
     */
    public function getBadges()
    {
        $data = $this->getDoctrine()->getRepository(ProductBadge::class)->findAll();
        if ($data) {
            return $this->jsonNormalized(['badges' => is_array($data) ? $data : [$data]]);
        } else {
            $errors['message'] = sprintf('Nem talált matricát.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
}