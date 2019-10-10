<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\ApiModel\ProductCategoryApiModel;
use App\Entity\ImageEntity;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Normalizer\DfrObjectSerializer;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/admin")
 */
class ProductCategoryApiController extends BaseController
{
    private $targetDirectory;
    private $em;
    /** @var Serializer  */
    protected $serializer;
    
    public function __construct(string $targetDirectory, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->targetDirectory = $targetDirectory;
        $this->em = $em;
    }
    
    //////////////////////////////////////////////////////////////////////////////////////
    ///                                                                                ///
    ///                                  CATEGORY API                                  ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////

    /**
     * @Route("/api/product/categories/", name="api-product-getCategories", methods={"GET"})
     */
    public function apiGetCategories()
    {
        $data = $this->getDoctrine()->getRepository(ProductCategory::class)->findAllOrdered();
        if ($data) {
//            $models = [];
//            foreach ($data as $product) {
//                $models[] = $this->createCategoryApiModel($product);
//            }
//            return $this->serializeIntoJsonResponse([
//                'products' => $models
//            ]);
//            return $this->serializeIntoJsonResponse(['categories' => $models]);
            
            return $this->jsonNormalized(['categories' => $data]);
        } else {
            $errors['message'] = sprintf('Nem talált egy kategóriát sem.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * @Route("/api/product/categories/{id}", name="api-product-getCategory", methods={"GET"})
     */
    public function apiGetCategory(Request $request)
    {
        $id = $request->attributes->get('id');
        $data = $this->getDoctrine()->getRepository(ProductCategory::class)->find($id);
        if ($data) {
            return $this->jsonNormalized(['categories' => [$data]]);
        } else {
            $errors['message'] = sprintf('Nem talált ilyen kategóriát: id=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
        
//        $categoryModel = $this->createCategoryApiModel($category);
//        return $this->serializeIntoJsonResponse($categoryModel);
    }
    
    /**
     * @Route("/api/product/categories/{id}/products/", name="api-product-getProductsInCategory", methods={"GET"})
     */
    public function apiGetProductsInCategory(Request $request)
    {
        $id = $request->attributes->get('id');
        $category = $this->getDoctrine()->getRepository(ProductCategory::class)->find($id);
        $data = $category->getProducts()->getValues();
        if ($data) {
            return $this->jsonObjNormalized(['products' => $this->toArray($data)], 200, ['groups' => 'productList']);
        } else {
            $errors['message'] = sprintf('Nem talált ilyen kategóriát: id=%s', $id);
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    /**
     * @Route("/api/product/categories/", name="api-product-newCategory", methods={"POST"})
     */
    public function apiNewCategory(Request $request, FileUploader $fileUploader, ValidatorInterface $validator)
    {
        $serializer = $this->serializer;
        
        $productCategory = new ProductCategory();
        $category = $serializer->deserialize($request->getContent(),ProductCategory::class,'json',[
                'object_to_populate' => $productCategory,
                'skip_null_values' => true,
            ]);
//        dd($productCategory);
        if ($category->getParent()) {
            if ($category->getParent()->getId()) {
                $productCategory->setParent($this->getDoctrine()->getRepository(ProductCategory::class)->find($category->getParent()->getId()));
            }
        }
        if ($category->getImage()) {
            if ($category->getImage()->getId()) {
                $productCategory->setImage($this->getDoctrine()->getRepository(ImageEntity::class)->find($category->getImage()->getId()));
            }
        }
        
        $violations = $validator->validate($productCategory);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->jsonNormalized(['errors' => $errors,], 422);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($productCategory);
        $em->flush();
    
        return $this->jsonNormalized(['categories' => [$productCategory]]);
//        $categoryModel = $this->createCategoryApiModel($category);
//        $response = $this->serializeIntoJsonResponse($categoryModel);
        // setting the Location header... it's a best-practice
//        $response->headers->set(
//            'Location',
//            $this->generateUrl('api-product-getCategory', ['id' => $category->getId()])
//        );
//        return $response;
    }
    
    /**
     * @Route("/api/product/categories/{id}", name="api-product-updateCategory", methods={"PUT"})
     */
    public function apiUpdateCategory(Request $request, ProductCategory $productCategory, FileUploader $fileUploader,
                                   ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $serializer = $this->serializer;
        $category = $serializer->deserialize($request->getContent(),ProductCategory::class,'json',[
                'object_to_populate' => $productCategory,
                'skip_null_values' => true,
            ]);
    
//        $data = json_decode($request->getContent());
//        $category = $serializer->denormalize($data,ProductCategory::class,'stdClass', [
//                'object_to_populate' => $productCategory,
//                'skip_null_values' => true,
//            ]);
    
//        $productCategory->setName($category->getName());
//        $productCategory->setEnabled($category->getEnabled());
        if ($category->getParent()) {
            if ($category->getParent()->getId()) {
                $productCategory->setParent($this->getDoctrine()->getRepository(ProductCategory::class)->find($category->getParent()->getId()));
            }
        }
        if ($category->getImage()) {
            if ($category->getImage()->getId()) {
                $productCategory->setImage($this->getDoctrine()->getRepository(ImageEntity::class)->find($category->getImage()->getId()));
            }
        }
        
        $violations = $validator->validate($productCategory);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->jsonNormalized(['errors' => $errors,], 422);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($productCategory);
        $em->flush();

        return $this->jsonNormalized(['categories' => [$productCategory]]);
    }
    
    /**
     * @Route("/api/product/categories/{id}", name="api-product-deleteCategory", methods={"DELETE"})
     */
    public function apiDeleteProductCategory(Request $request)
    {
        //        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $request->attributes->get('id');
        $data = $this->getDoctrine()->getRepository(ProductCategory::class)->find($id);
        if ($data) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($data);
            $em->flush();
            return new Response(null, 204);
        } else {
            $errors['message'] = sprintf('Nem létező kategóriát nem lehet törölni.');
            return $this->jsonNormalized(['errors' => [$errors]], 422);
        }
    }
    
    

    /**
     * Turns a Category into a CategoryApiModel for the API.
     * This could be moved into a service if it needed to be re-used elsewhere.
     */
    private function createCategoryApiModel(ProductCategory $category)
    {
        $model = new ProductCategoryApiModel();
        $model->id = $category->getId();
        $model->name = $category->getName();
        $model->slug = $category->getSlug();
        $model->description = $category->getDescription();
        $model->parent = $category->getParent();
            //? $category->getParent()->getId() : '';
        $model->parentName = $category->getParent() ? $category->getParent()->getName() : '';
        $model->enabled = $category->isEnabled();

        $model->setImage($category->getImage());
            //? $category->getImage()->getId() : null);
        $model->setImageUrl('/uploads/images/categories/'.$category->getImage()->getFile());   // ?? IDEIGLENES URL kepzes
        $urlToEdit = $this->generateUrl(
            'api-product-getCategory',
            ['id' => $category->getId()]
        );
        $urlToView = $this->generateUrl(
            'site-product-list',
            ['slug' => $category->getSlug()]
        );
        $model->setUrlToEdit($urlToEdit);
        $model->setUrlToView($urlToView);
        return $model;
    }
}