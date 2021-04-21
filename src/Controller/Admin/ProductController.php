<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Product\Product;
use App\Entity\Price;
use App\Entity\Product\ProductBadge;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductKind;
use App\Entity\Product\ProductStatus;
use App\Entity\VatRate;
use App\Form\ProductFilterType;
use App\Form\ProductFormType;
use App\Form\ProductQuantityFormType;
use App\Services\FileUploader;
use App\Services\StoreSettings;
use Doctrine\Common\Annotations\AnnotationReader;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
     * @Route("/products/filter", name="product-list-filter")
     */
    public function handleFilterForm(Request $request)
    {
        $form = $this->createForm(ProductFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $searchTerm = null;
            $status = null;

            if ($filters['searchTerm']) {
                $searchTerm = $filters['searchTerm'];
            }
            if ($filters['status']) {
                $status = $this->getDoctrine()->getRepository(ProductStatus::class)->find($filters['status'])->getShortcode();
            }
            return $this->redirectToRoute('product-list',[
                'searchTerm' => $searchTerm,
                'status' => $status,
            ]);
        }
        return $this->redirectToRoute('product-list');
    }

    /**
     * @Route("/products", name="product-list",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function listProducts(Request $request, $page = 1, StoreSettings $settings)
    {
        $searchTerm = $request->query->get('searchTerm');
        $status = $request->query->get('status');
        $page = $request->query->get('page') ? $request->query->get('page') : $page;
//        dd($request->attributes->get('_route_params'));
//        dd($request->query->all());

        $filterTags = [];
        $urlParams = [];
        $data = $filterTags;
        $itemCounts = [];
        $em = $this->getDoctrine();
        if ($searchTerm) {
            $filterTags['searchTerm'] = 'Keresés: '.$searchTerm;
            $data['searchTerm'] = $searchTerm;
            $urlParams['searchTerm'] = $searchTerm;
        }
        if ($status) {
            $filterTags['status'] = $em->getRepository(ProductStatus::class)->findOneBy(['shortcode' => $status])->getName();
            $urlParams['status'] = $status;
            $data['status'] = $em->getRepository(ProductStatus::class)->findOneBy(['shortcode' => $status]);
        }
        $filterForm = $this->createForm(ProductFilterType::class, $data);

        $filterUrls = [];
        foreach ($filterTags as $key => $value) {
            // remove the current filter from the urlParams
            $shortlist = array_diff_key($urlParams,[$key => '']);

            // generate the URL with the remaining filters
            $filterUrls[$key] = $this->generateUrl('product-list',[
                'searchTerm' => isset($shortlist['searchTerm']) ? $shortlist['searchTerm'] : null,
                'status' => isset($shortlist['status']) ? $shortlist['status'] : null,
            ]);
        }

        $filterQuickLinks['all'] = [
            'name' => 'Összes termék',
            'url' => $this->generateUrl('product-list'),
            'active' => false,
            'itemCount' => $em->getRepository(Product::class)->count(['status' => !null]),
        ];
        $filterQuickLinks['enabled'] = [
            'name' => 'Engedélyezett',
            'url' => $this->generateUrl('product-list', ['status' => ProductStatus::STATUS_ENABLED]),
            'active' => false,
            'itemCount' => $em->getRepository(Product::class)->count(['status' => $em->getRepository(ProductStatus::class)->findOneBy(['shortcode' => ProductStatus::STATUS_ENABLED])]),
        ];
        $filterQuickLinks['unavailable'] = [
            'name' => 'Kifutott',
            'url' => $this->generateUrl('product-list', ['status' => ProductStatus::STATUS_UNAVAILABLE]),
            'active' => false,
            'itemCount' => $em->getRepository(Product::class)->count(['status' => $em->getRepository(ProductStatus::class)->findOneBy(['shortcode' => ProductStatus::STATUS_UNAVAILABLE])]),
        ];
        $filterQuickLinks['removed'] = [
            'name' => 'Törölt',
            'url' => $this->generateUrl('product-list', ['status' => ProductStatus::STATUS_REMOVED]),
            'active' => false,
            'itemCount' => $em->getRepository(Product::class)->count(['status' => $em->getRepository(ProductStatus::class)->findOneBy(['shortcode' => ProductStatus::STATUS_REMOVED])]),
        ];

        // Generate the quicklinks which are placed above the filter
        $hasCustomFilter = false;
        if (!$searchTerm && !$status) {
            $filterQuickLinks['all']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$searchTerm && $status && $status == ProductStatus::STATUS_ENABLED) {
            $filterQuickLinks['enabled']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$searchTerm && $status && $status == ProductStatus::STATUS_UNAVAILABLE) {
            $filterQuickLinks['unavailable']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$searchTerm && $status && $status == ProductStatus::STATUS_REMOVED) {
            $filterQuickLinks['removed']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$hasCustomFilter) {
            $filterQuickLinks['custom'] = [
                'name' => 'Egyedi szűrés',
                'url' => $this->generateUrl('product-list',$request->query->all()),
                'active' => true,
                'itemCount' => $em->getRepository(Product::class)->countAll([
                    'searchTerm' => $searchTerm,
                    'status' => $status,
                    ]),
            ];
        }


        $queryBuilder = $em->getRepository(Product::class)->findAllQuery([
            'searchTerm' => $searchTerm,
            'status' => $status,
        ]);

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $products = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $products[] = $result;
        }

        if (!$products) {
//            $this->addFlash('danger', 'Nem talált termékeket! Próbáld módosítani a szűrőket.');
        }

//        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
//
//        if (!$products) {
//            //throw $this->createNotFoundException('Nem talált egy terméket sem!');
//
//            $this->addFlash('success', 'Nem talált egy terméket sem! ');
//            return $this->redirectToRoute('product-list');
//        }
//        // render a template, then in the template, print things with {{ termek.munkatars }}
//        foreach($products as $i => $item) {
//            // $products[$i] is same as $item
//            $products[$i]->getCreatedAt()->format('Y-m-d H:i:s');
//            $products[$i]->getUpdatedAt()->format('Y-m-d H:i:s');
//        }

        return $this->render('admin/product/product_list.html.twig', [
            'products' => $products,
            'title' => 'Termékek',
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
//            'count' => count($orders),
//            'orderCount' => empty($orders) ? 'Nincsenek rendelések' : count($orders),
            'filterQuickLinks' => $filterQuickLinks,
            'filterForm' => $filterForm->createView(),
            'filterTags' => $filterTags,
            'filterUrls' => $filterUrls,
            ]);
    }

    /**
     * @Route("/product/new", name="product-new")
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Sikeresen elmentetted az új terméket!');
            return $this->redirectToRoute('product-list');
        }

        $product->setBackToList($this->generateUrl('product-list'));

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(ProductCategory::class)->findAll();
        $badges = $em->getRepository(ProductBadge::class)->findAll();
        $statuses = $em->getRepository(ProductStatus::class)->findAll();
        $kinds = $em->getRepository(ProductKind::class)->findAll();

        return $this->render('admin/product/product_edit.html.twig', [
//            'form' => $form->createView(),
            'product' => $product,
            'productJson' => $this->createJson($product, ['groups' => 'productView']),
            'statusesJson' => $this->createJsonNormalized($statuses),
            'categoriesJson' => $this->createJsonNormalized($categories),
            'badgesJson' => $this->createJsonNormalized($badges),
            'kindsJson' => $this->createJson($kinds, ['groups' => 'productView']),
        ]);
    }

    /**
     * @Route("/products/edit/{id}", name="product-edit")
     *  requirements={"id"="\d+"}
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
//            dd($product);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();  // PriceVersioning::onFlush() event is triggered, see service.yaml

            $this->addFlash('success', 'Sikeresen módosítottad a terméket!');

//            return $this->redirectToRoute('product-edit',['id' => $product->getId()]);
        }

        $product->setBackToList($this->generateUrl('product-list'));

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(ProductCategory::class)->findAll();
        $badges = $em->getRepository(ProductBadge::class)->findAll();
        $statuses = $em->getRepository(ProductStatus::class)->findAll();
        $kinds = $em->getRepository(ProductKind::class)->findAll();

        return $this->render('admin/product/product_edit.html.twig', [
//            'form' => $form->createView(),
            'product' => $product,
            'productJson' => $this->createJson($product, ['groups' => 'productView']),
            'statusesJson' => $this->createJsonNormalized($statuses),
            'categoriesJson' => $this->createJsonNormalized($categories),
            'badgesJson' => $this->createJsonNormalized($badges),
            'kindsJson' => $this->createJson($kinds, ['groups' => 'productView']),
        ]);
    }

    private function createJson($data, array $context = [])
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $objNormalizer = new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor(),
            null,null,
            [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $content) {
                    return $object->getName();
//                    if ($object instanceof ProductCategory) { return ['id' => $object->getId(), 'name' => $object->getName()]; }
//                    return $object->getId();
                }
            ]);
        $serializer = new Serializer([$objNormalizer], [new JsonEncoder()]);
        $json = $serializer->serialize($data, 'json', $context);
        return $json;
    }

    protected function createJsonNormalized($data, array $context = [])
    {
        $jsonNormalizer = new JsonSerializableNormalizer(
            null,
            null,
            array(JsonSerializableNormalizer::CIRCULAR_REFERENCE_HANDLER=>function ($object) {
                if ($object instanceof ProductCategory) {
                    return ['id' => $object->getId(), 'name' => $object->getName()];
                }
                return $object->getId();

            })
        );
        $serializer = new Serializer([$jsonNormalizer], [new JsonEncoder()]);
        $json = $serializer->serialize($data, 'json', $context);
        return $json;
    }
    
//    /**
//     * @Route("/product/delete/{id}", name="product-delete", methods={"GET"})
//     */
//    public function deleteProduct(Product $product)
//
//    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
//        $em = $this->getDoctrine()->getManager();
//        $em->remove($product);
//        $em->flush();
//
//        return $this->redirectToRoute('product-list');
//    }
//
//    /**
//     * @Route("/product/editStock/{id}", name="product-edit-stock")
//     */
//    public function editStock(Request $request, Product $product)
//    {
//        $form = $this->createForm(ProductQuantityFormType::class, $product);
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $product = $form->getData();
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($product);
//            $em->flush();
//
//            /**
//             * If AJAX request, renders and returns an HTML with the value
//             */
//            if ($request->isXmlHttpRequest()) {
//                return $this->render('admin/item.html.twig', [
//                    'item' => $product->getStock(),
//                ]);
//            }
////            $this->addFlash('success', 'A mennyiség sikeresen frissítve.');
//            return $this->redirectToRoute('product-list');
//        }
//
//        /**
//         * If AJAX request and the form was submitted, renders the form, fills it with data
//         * Also renders errors!
//         * (!?, there is a validation error)
//         */
//        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
//            $html = $this->renderView('admin/product/_stock_inline_form.html.twig', [
//                'form' => $form->createView()
//            ]);
//            return new Response($html,400);
//
//        }
//        /**
//         * Renders form initially with data
//         */
//        return $this->render('admin/product/_stock_inline_form.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }

}