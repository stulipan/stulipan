<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Product\ProductAttribute;
use App\Entity\Product\ProductKind;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class ProductKindApiController extends BaseController
{
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                              Product Kind API                                  ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Route("/api/product/kinds/", name="api-product-getKinds", methods={"GET"})
     */
    public function getKinds()
    {
        $data = $this->getDoctrine()->getRepository(ProductKind::class)->findAll();
        if ($data) {
            return $this->jsonObjNormalized(['kinds' => $this->toArray($data)], 200, ['groups' => 'productView']);
        } else {
            $errors['message'] = sprintf('Nem talált terméktípust.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * @Route("/api/product/kinds/{id}/attributes/", name="api-product-getAttributesByKind", methods={"GET"})
     */
    public function getAttributesByKind(Request $request, ProductKind $kind)
    {
        $data = $this->getDoctrine()->getRepository(ProductAttribute::class)->findBy(['kind' => $kind], ['ordering' => 'ASC']);
        if ($data) {
//            dd($data);
            return $this->jsonObjNormalized(['attributes' => $this->toArray($data)], 200, ['groups' => 'productView']);
        } else {
            $errors['message'] = sprintf('Nem talált termékváltozatot.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
}