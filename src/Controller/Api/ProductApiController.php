<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\ApiModel\ProductApiModel;
use App\Entity\ApiModel\ProductCategoryApiModel;
use App\Entity\Product\Product;
use App\Normalizer\DfrObjectSerializer;
use App\Serializer\ImageEntityDenormalizer;
use App\Serializer\PriceDenormalizer;
use App\Serializer\ProductBadgeDenormalizer;
use App\Serializer\ProductCategoryDenormalizer;
use App\Serializer\ProductDenormalizer;
use App\Serializer\ProductImageDenormalizer;
use App\Serializer\ProductKindDenormalizer;
use App\Serializer\ProductStatusDenormalizer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/admin")
 */
class ProductApiController extends BaseController
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
}