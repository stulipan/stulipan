<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\ApiModel\ProductCategoryApiModel;
use App\Entity\ImageEntity;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Form\ProductCategoryFormType;
use App\Normalizer\DfrObjectNormalizer;
use App\Normalizer\DfrObjectSerializer;
use App\Normalizer\ProductCategoryNormalizer;
use App\Pagination\PaginatedCollection;
use App\Services\FileUploader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @Route("/admin")
 */
class ProductCategoryController extends BaseController
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
    ///                                      CATEGORY                                  ///
    ///                                                                                ///
    //////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * @Route("/product/newCategory/", name="product-newCategory")
     */
    public function newProductCategory(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(ProductCategoryFormType::class);
        $title = 'Új kategória';

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            // $file stores the uploaded file which is an UploadedFile object
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */

            $file = $form['imageFile']->getData();
            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, null, FileUploader::IMAGE_OF_CATEGORY_TYPE);
                $img = new ImageEntity();
                $img->setFile($newFilename);
                $category->setImage($img);
//                $category->setImage($newFilename);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Kategória sikeresen elmentve!');

            return $this->redirectToRoute('product-listCategories');
        }
        return $this->render('admin/product/product-category-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/product/editCategory/{id}", name="product-editCategory")
     */
    public function editProductCategory(Request $request, ProductCategory $category, FileUploader $fileUploader)
    {
        $form = $this->createForm(ProductCategoryFormType::class, $category);
        $title = 'Kategória módosítása';

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form['imageFile']->getData();

            if (!is_null($file)) {
                $newFilename = $fileUploader->uploadFile($file, $category->getImage(), FileUploader::IMAGE_OF_CATEGORY_TYPE); //2nd param = null, else deletes prev image
                $img = new ImageEntity();
                $img->setFile($newFilename);
                $category->setImage($img);
//                $category->setImage($newFilename);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Kategória sikeresen elmentve!');

            return $this->redirectToRoute('product-listCategories');
        }
        return $this->render('admin/product/product-category-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/product/category/{page}", name="product-listCategories", requirements={"page"="\d+"})
     */
    public function listProductCategories($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(ProductCategory::class)
            ->findAllQueryBuilder();
        $imageDirectory =  $this->getParameter('category_images_directory');

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        //On the Pagerfanta object, call setMaxPerPage() to set displayed items to 10, the set current page
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $categories = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $categories[] = $result;
        }

        if (!$categories) {
            throw $this->createNotFoundException(
                'Nem talált egy termékkategoriát sem!'
            );
        }

        $paginatedCollection = new PaginatedCollection($categories, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ szamla.munkatars }}
        return $this->render('admin/product/category-list.html.twig', [
            'categories' => $categories,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($categories),
            'title' => 'Kategóriák',
            'imageDir' => $imageDirectory,
        ]);
    }

    /**
     * @Route("/product/deleteCategory/{id}", name="product-deleteCategory", methods={"GET"})
     */
    public function deleteProductCategory(ProductCategory $category)

    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('product-listCategories');
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
    
//    /**
//     * @Route("/api/product/categories/{id}", name="api-product-editCategory", methods={"PUT"})
//     */
//    public function updateCategory(Request $request, FileUploader $fileUploader,
//                                   ValidatorInterface $validator, EntityManagerInterface $entityManager) //ProductCategory $productCategory,
//    {
//
//        $id = $request->attributes->get('id');
//        $productCategory = $this->getDoctrine()->getRepository(ProductCategory::class)->find($id);
//
//        $data = json_decode($request->getContent());
////        dd($data);
//        $uow = $entityManager->getUnitOfWork();
////                dd($uow->getScheduledEntityUpdates());
////        dd($uow->getScheduledEntityInsertions());
//
//        $classMetadataFactory = new ClassMetadataFactory(
//            new AnnotationLoader(
//                new AnnotationReader()
//            )
//        );
//        $objNormalizer = new ObjectNormalizer(
//            $classMetadataFactory,
//            null,
//            null,
//            new PhpDocExtractor(),
//            null,
//            null,
//            [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT]
//        );
//        $myNormalizer = new ProductCategoryNormalizer($this->em);
//
//        $normalizer = [
////            new JsonSerializableNormalizer(),
//            new ArrayDenormalizer(),
//            $myNormalizer,
//            $objNormalizer,
//        ];
//        $serializer = new Serializer($normalizer, [new JsonEncoder()]);
//
//        $category = $serializer->denormalize(
//            $data,
//            ProductCategory::class,
//            'stdClass',
//            [
//                'skip_null_values' => true,
//            ]
//        );
//
//        $productCategory->setName($category->getName());
//        $productCategory->setParent($this->getDoctrine()->getRepository(ProductCategory::class)->find($category->getParent()->getId()));
//        $productCategory->setEnabled($category->getEnabled());
//
////        dd($productCategory);
//
////        $categoryReflection = new \ReflectionObject($category);
////        $productCategoryReflection = new \ReflectionObject($productCategory);
////        $p = $productCategoryReflection->getProperties();
//////        dd($productCategoryReflection->getMethods());
////        foreach ($productCategoryReflection->getMethods() as $method)
////        {
////            dd($method->getName());
////
////        }
////
////            foreach ($categoryReflection->getProperties() as $prop)
////        {
////            $prop->setAccessible(true);
////            dd($prop);
////            $name=$prop->getName();
////            $value=$prop->getValue($category);
////
////            $prop2 = $productCategoryReflection->getProperty($name);
////            $prop2->setAccessible(true);
////            $value2 = $prop2->getValue($productCategory);
////
//////            dd($productCategoryReflection->hasProperty($name));
//////            dd($p[$name]);
////
////            if ($value !== $value2) {
////
////                $productCategory->setId($value2);
////            }
////            dd($name);
////        }
//
//
////        dd($category);
////        dd($productCategory);
//
////        $serializer->deserialize(
////            $request->getContent(),
////            ProductCategory::class,
////            'json',
////            [
////                'object_to_populate' => $productCategory,
////                'skip_null_values' => true,
////            ]);
//
////        $category =  $serializer->deserialize(
////            $request->getContent(),
////            ProductCategory::class,
////            'json');
////        dd($category);
////        dd($productCategory->getImage());
//
//        $cat2 = $this->getDoctrine()->getRepository(ProductCategory::class)->find(2);
////        $productCategory->setParent($cat2);
////        dd($productCategory);
//
////        $category = new ProductCategory();
////        $form = $this->createForm(ProductCategoryFormType::class, $productCategory, ['csrf_protection' => false,]);
////        $form->submit($data);
//////        dd($data);
//////        dd($form->getData());
////        if (!$form->isValid()) {
////            $errors = $this->getErrorsFromForm($form);
////            return $this->serializeIntoJsonResponse([
////                'errors' => $errors
////            ], 400);
////        }
////        /** @var ProductCategory $category */
////        $category = $form->getData();
////        dd($category);
//
//        $violations = $validator->validate($productCategory);
//        if ($violations->count() > 0) {
//            $errors = [];
//            foreach ($violations as $violation) {
//                $errors[$violation->getPropertyPath()] = $violation->getMessage();
//            }
//            return $this->jsonNormalized(['errors' => $errors,], 422);
//        }
//
//        $em = $this->getDoctrine()->getManager();
////        $em->refresh($productCategory);
////        dd($productCategory);
//        $em->persist($productCategory);
//
////        dd($uow->getIdentityMap());
////        dd($uow->getScheduledEntityUpdates());
////        dd($uow->getScheduledEntityInsertions());
//        $em->flush();
//
//        dd('ff');
//
//        $em->refresh($productCategory);
//        return $this->jsonNormalized(['categories' => [$productCategory]]);
//
////        $categoryModel = $this->createCategoryApiModel($category);
////        $response = $this->serializeIntoJsonResponse($categoryModel);
////        // setting the Location header... it's a best-practice
////        $response->headers->set(
////            'Location',
////            $this->generateUrl('api-product-getCategory', ['id' => $category->getId()])
////        );
////        return $response;
//    }
}