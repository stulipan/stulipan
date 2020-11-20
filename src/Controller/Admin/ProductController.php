<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\PaymentStatus;
use App\Entity\Product\Product;
use App\Entity\Price;
use App\Entity\Product\ProductStatus;
use App\Entity\VatRate;
use App\Form\OrderFilterType;
use App\Form\ProductFilterType;
use App\Form\ProductFormType;
use App\Form\ProductQuantityFormType;
use App\Services\FileUploader;
use App\Services\StoreSettings;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
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
     * @ Route("/termek/", name="product-list")
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

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Sikeresen elmentetted az új terméket!');
            return $this->redirectToRoute('product-list');
        }

        return $this->render('admin/product/product_edit.html.twig', [
//            'form' => $form->createView(),

        ]);
    }


    /**
     * @Route("/product/edit/{id}", name="product-edit")
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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();  // PriceVersioning::onFlush() event is triggered, see service.yaml

            $this->addFlash('success', 'Sikeresen módosítottad a terméket!');

//            return $this->redirectToRoute('product-edit',['id' => $product->getId()]);
        }

        return $this->render('admin/product/product_edit.html.twig', [
//            'form' => $form->createView(),
            'product' => $product,
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