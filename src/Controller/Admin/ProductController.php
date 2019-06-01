<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Controller\Utils\GeneralUtils;
use App\Entity\ApiModel\ProductApiModel;
use App\Entity\ApiModel\ProductCategoryApiModel;
use App\Entity\ImageEntity;
use App\Entity\Model\ErrorEntity;
use App\Entity\Product\Product;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductCategory;
use App\Entity\Price;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductStatus;
use App\Entity\VatRate;
use App\Form\ImageType;
use App\Form\ProductCategoryFormType;
use App\Form\ProductFormType;
use App\Form\ProductQuantityFormType;
use App\Normalizer\DfrObjectSerializer;
use App\Pagination\PaginatedCollection;
use App\Serializer\ImageEntityDenormalizer;
use App\Serializer\PriceDenormalizer;
use App\Serializer\ProductBadgeDenormalizer;
use App\Serializer\ProductCategoryDenormalizer;
use App\Serializer\ProductCategoryNormalizer;
use App\Serializer\ProductCategoryNormalizerObj;
use App\Serializer\ProductDenormalizer;
use App\Serializer\ProductImageDenormalizer;
use App\Serializer\ProductKindDenormalizer;
use App\Serializer\ProductKindNormalizer;
use App\Serializer\ProductNormalizer;
use App\Serializer\ProductStatusDenormalizer;
use App\Serializer\ProductStatusNormalizer;
use App\Services\FileUploader;
use Doctrine\Common\Annotations\AnnotationReader;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @Route("/admin")
 */
class ProductController extends BaseController
{
    private $targetDirectory;
    
    public function __construct(string $targetDirectory)
    {
        parent::__construct();
        $this->targetDirectory = $targetDirectory;
    }
    
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                                  PRODUCT API                                   ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Route("/api/products/", name="api-product-getProducts", methods={"GET"})
     */
    public function apiGetProducts()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        if ($products) {
            return $this->jsonObjNormalized(['products' => $products],200, ['groups' => 'productList']);
        } else {
            $errors['message'] = sprintf('Nem talált termékeket.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
//        $models = [];
//        foreach ($products as $product) { $models[] = $this->createProductApiModel($product); }
//        return $this->serializeIntoJsonResponse(['products' => $models]);
    }
    
    /**
     * Example: /admin/api/products/1
     *
     * @Route("/api/products/{id}", name="api-product-getProduct", methods={"GET"})
     */
    public function apiGetProduct(Request $request)
    {
        $id = $request->attributes->get('id');
        $data = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if ($data) {
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $normalizer = [
//                new ObjectNormalizer($classMetadataFactory, null, null, new PhpDocExtractor(), null, null, [ObjectNormalizer::CIRCULAR_REFERENCE_LIMIT => 1]),
////                new ProductNormalizer(),
////                new ProductCategoryNormalizerObj($classMetadataFactory),
////                new ProductKindNormalizer(),
////                new ProductStatusNormalizer(),
////                new JsonSerializableNormalizer(), // to normalize Price entity
//            ];
//            $serializer = new Serializer($normalizer, [new JsonEncoder()]);
//            $json = $serializer->serialize($data, 'json', ['groups' => 'productView']);
//            return new JsonResponse($json, 200, [], true);

            $data->setBackToList($this->generateUrl('product-list'));
            return $this->jsonObjNormalized(['products' => [$data]], 200, ['groups' => 'productView']);
            
        } else {
            $errors['message'] = sprintf('Nem talált ilyen terméket: id=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * @Route("/api/products/{id}", name="api-product-deleteProduct", methods={"DELETE"})
     */
    public function apiDeleteProduct(Request $request)
    {
        //        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->attributes->get('id');
        $data = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if ($data) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($data);
            $em->flush();
            return new Response(null, 204);
        } else {
            $errors['message'] = sprintf('Nem létező terméket nem lehet törölni.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * @Route("/api/products/", name="api-product-newProduct", methods={"POST"})
     */
    public function apiNewProduct(Request $request, ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();
        $normalizer = [
            new ProductDenormalizer($em),
            new ProductCategoryDenormalizer($em),
            new ProductKindDenormalizer($em),
            new ProductStatusDenormalizer($em),
            new ProductBadgeDenormalizer($em),
            new ProductImageDenormalizer($em),
            new ImageEntityDenormalizer($em),
            new PriceDenormalizer($em),
            new ArrayDenormalizer(),
        ];
        $serializer = new Serializer($normalizer, [new JsonEncoder()]);
        
        /** @var Product $product */
        $product = $serializer->denormalize(json_decode($request->getContent(), true),Product::class,'json',[
//            'skip_null_values' => true,
        ]);
    
        $errors = $this->getValidationErrors($product, $validator);
        if (!empty($errors)) {
            return $this->jsonNormalized(['errors' => $errors], 422);
        }
//        dd($product);
    
        $em->persist($product);
        $em->flush();
//        dd('flush');
    
        //CELSZERUBB csak a termeket visszaadni, es nem reloadolni a teljes oldalt
        return $this->jsonObjNormalized(['products' => [$product]], 200, ['groups' => 'productView']);
        
//        // LEHET IGY, hogy visszaadja a termeket es legeneralja (reloadolja) a teljes oldalt:
//        $response = $this->jsonNormalized(['products' => [$product]]);
//        // setting the Location header... it's a best-practice
//        $response->headers->set('Location', $this->generateUrl('api-product-getCategory', ['id' => $category->getId()]));
//        return $response;
    }
    
    /**
     * @Route("/api/products/{id}", name="api-product-updateProduct", methods={"PUT"})
     */
    public function apiUpdateProduct(Request $request, Product $product, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }
//        dd($data);
        
        $em = $this->getDoctrine()->getManager();
        $normalizer = [
            new ProductDenormalizer($em),
            new ProductCategoryDenormalizer($em),
            new ProductKindDenormalizer($em),
            new ProductStatusDenormalizer($em),
            new ProductBadgeDenormalizer($em),
            new PriceDenormalizer($em),
            new ProductImageDenormalizer($em),
            new ImageEntityDenormalizer($em),
            new ArrayDenormalizer(),
        ];
        $serializer = new Serializer($normalizer, [new JsonEncoder()]);
        $serializer->deserialize($request->getContent(),Product::class,'json',[
            'object_to_populate' => $product,
//            'skip_null_values' => true,
        ]);
        

//        dd($product);
//        $form = $this->createForm(ProductFormType::class, $product, ['csrf_protection' => false,]);
//        dd($data);
//        $form->submit($data);
////        dd($form->getErrors());
//        if (!$form->isValid()) {
//            $errors = $this->getErrorsFromForm($form);
//            return $this->jsonNormalized(['errors' => $errors], 400);
//        }
//
//        /** @var Product $product */
//        $product = $form->getData();
//        dd($product);
        
        
        $errors = $this->getValidationErrors($product, $validator);
        if (!empty($errors)) {
            return $this->jsonNormalized(['errors' => $errors], 422);
        }
        
        $em->persist($product);
        $em->flush();
        return $this->jsonObjNormalized(['products' => [$product]],200, ['groups' => 'productView']);
    }
    
    /**
     * Turns a Product into a ProductApiModel for the API.
     *
     * This could be moved into a service if it needed to be
     * re-used elsewhere.
     *
     * @param Product $product
     * @return ProductApiModel
     */
    private function createProductApiModel(Product $product)
    {
        $model = new ProductApiModel();
        $model->id = $product->getId();
        $model->productName = $product->getProductName();
        $model->sku = $product->getSku();

        $model->setPrice($product->getPrice()->getGrossPrice());
        $model->setStatus($product->getStatus()->getName());
        $model->setImage('/uploads/images/termekek/'.$product->getImage());
        $model->setStock($product->getStock());
        $selfUrl = $this->generateUrl(
            'api-product-get',
            ['id' => $product->getId()]
        );
        $model->setUrl($selfUrl);
        return $model;
    }
    
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