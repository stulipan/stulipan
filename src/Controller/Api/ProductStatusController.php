<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Product\ProductStatus;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class ProductStatusController extends BaseController
{
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                           Product Statuses API                                 ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Route("/api/product/statuses", name="api-product-getStatuses", methods={"GET"})
     */
    public function getStatuses()
    {
        $data = $this->getDoctrine()->getRepository(ProductStatus::class)->findAll();
        if ($data) {
            return $this->jsonNormalized(['statuses' => $data]);
        } else {
            $errors['message'] = sprintf('Nem talált termékállapotot.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
}