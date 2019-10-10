<?php

namespace App\Controller\Admin;

use App\Entity\Product\Product;
use App\Entity\Price;
use App\Entity\VatRate;
use App\Form\ProductFormType;
use App\Form\ProductQuantityFormType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ProductController extends AbstractController
{
    
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                                  Product                                       ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Route("/termek/", name="product-list")
     */
    public function listActionOld()
    {
        $termek = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();

        if (!$termek) {
            //throw $this->createNotFoundException('Nem talált egy terméket sem!');

            $this->addFlash('success', 'Nem talált egy terméket sem! ');
            return $this->redirectToRoute('product-list');
        }
        // render a template, then in the template, print things with {{ termek.munkatars }}
        foreach($termek as $i => $item) {
            // $termek[$i] is same as $item
            $termek[$i]->getCreatedAt()->format('Y-m-d H:i:s');
            $termek[$i]->getUpdatedAt()->format('Y-m-d H:i:s');
        }

        return $this->render('admin/product/product_list.html.twig', [
            'products' => $termek,
            'title' => 'Termékek',
            ]);
    }


    /**
     * @Route("/product/newProduct", name="product-new")
     */
    public function newProduct(Request $request, FileUploader $fileUploader)
    {
        $product = new Product();
        $price = new Price();
//        $price->setProduct($product);
        $vatRate = $this->getDoctrine()->getRepository(VatRate::class)->find(1);
        $price->setVatRate($vatRate);
        $product->setPrice($price);
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = $form->getData();
            $product->setRank(10);
//            $product->getPrice()->setProduct($product);
//            $product->getPrice()->setActivated(true);

//            if ($product->hasSubproducts()) {
//                $product->getPrice()->setType(Price::PRICE_FOR_SUBPRODUCT);
//            } else {
//                $product->getPrice()->setType(Price::PRICE_FOR_PRODUCT);
//            }

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form['imageFile']->getData();

            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, null); //2nd param = null, else deletes prev image
                $product->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Sikeresen elmentetted az új terméket!');
            return $this->redirectToRoute('product-list');
        }

        return $this->render('admin/product/product_edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Új termék hozzáadása',
            
        ]);
    }


    /**
     * @Route("/product/editProduct/{id}", name="product-edit")
     */
    public function editProduct(Request $request, Product $product, FileUploader $fileUploader)
    {
        $form = $this->createForm(ProductFormType::class, $product); //, ['id' => $formAdatok->getKind()->getId()]
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form['imageFile']->getData();

            if (!is_null($file)) {
//                $newFilename = $fileUploader->uploadFile($file, $product->getImage()); //2nd param = null, else deletes prev image
                $newFilename = $fileUploader->uploadFile($file, null); //2nd param = null, else deletes prev image
                $product->setImage($newFilename);
            }
            foreach ($product->getSubproducts() as $i => $subproduct) {
                if ($subproduct->getPrice() === null) {
                    $product->removeSubproduct($subproduct);
                } else {
                    if ($subproduct->getPrice()->getGrossPrice() === null || $subproduct->getPrice()->getGrossPrice() == 0) {
                        $product->removeSubproduct($subproduct);
                    } else {
//                        $subproduct->setProduct($product);
                        if (!$subproduct->getSku() || $subproduct->getSku() === null) {
                            $subproduct->setSku($product->getSku() . $i);
                        }
                    }
                }
            }

//            dd($product);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();  // PriceVersioning::onFlush() event is triggered, see service.yaml

            $this->addFlash('success', 'Sikeresen módosítottad a terméket!');

//            return $this->redirectToRoute('product-edit',['id' => $product->getId()]);
        }

        return $this->render('admin/product/product_edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'title' => 'Termék módosítása', 
        ]);
    }
    
    /**
     * @Route("/product/delete/{id}", name="product-delete", methods={"GET"})
     */
    public function deleteProduct(Product $product)
    
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        
        return $this->redirectToRoute('product-list');
    }

    /**
     * @Route("/product/editStock/{id}", name="product-edit-stock")
     */
    public function editStock(Request $request, Product $product)
    {
        $form = $this->createForm(ProductQuantityFormType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            /**
             * If AJAX request, renders and returns an HTML with the value
             */
            if ($request->isXmlHttpRequest()) {
                return $this->render('admin/item.html.twig', [
                    'item' => $product->getStock(),
                ]);
            }
//            $this->addFlash('success', 'A mennyiség sikeresen frissítve.');
            return $this->redirectToRoute('product-list');
        }

        /**
         * If AJAX request and the form was submitted, renders the form, fills it with data
         * Also renders errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('admin/product/_stock_inline_form.html.twig', [
                'form' => $form->createView()
            ]);
            return new Response($html,400);

        }
        /**
         * Renders form initially with data
         */
        return $this->render('admin/product/_stock_inline_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }



//        $data = json_decode($request->getContent(), true);
//        if ($data === null) {
//            throw new BadRequestHttpException('Invalid JSON');
//        }
//
//        $product = new Product();
//        $form = $this->createForm(ProductFormType::class, null, [
//            'csrf_protection' => false,
//        ]);
//        $form->submit($data);
//        if (!$form->isValid()) {
//            $errors = $this->getErrorsFromForm($form);
//            return $this->serializeIntoJsonResponse([
//                'errors' => $errors
//            ], 400);
//        }
//
//        /** @var Product $product */
//        $product = $form->getData();
////        $product->setUser($this->getUser());
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($product);
//        $em->flush();
//        $productModel = $this->createProductApiModel($product);
//        $response = $this->serializeIntoJsonResponse($productModel);
//        // setting the Location header... it's a best-practice
//        $response->headers->set(
//            'Location',
//            $this->generateUrl('api-product-get', ['id' => $product->getId()])
//        );
//        return $response;


//    /**
//     * @Route("/api/getProduct/{id}", name="api-product-get", methods={"GET"})
//     */
//    public function getProduct(Product $product)
//    {
//        $productModel = $this->createProductApiModel($product);
//        return $this->serializeIntoJsonResponse($productModel);
//    }
//    /**
//     * @Route("/api/deleteProduct/{id}", name="api-product-delete", methods={"DELETE"})
//     */
//    public function deleteProduct(Product $product)
//    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
//        $em = $this->getDoctrine()->getManager();
//        $em->remove($product);
//        $em->flush();
//        return new Response(null, 204);
//    }
}