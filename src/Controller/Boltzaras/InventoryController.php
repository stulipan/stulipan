<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Controller\Boltzaras;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a InventoryProduct entity-hez kapcsolodik es azzal dolgozik
use App\Entity\Inventory\InventoryProduct;
use App\Entity\Inventory\InventoryCategory;
use App\Entity\Inventory\InventorySupply;
use App\Entity\Inventory\InventorySupplyItem;
use App\Entity\Inventory\InventoryWaste;
use App\Entity\Inventory\InventoryWasteItem;
use App\Form\Inventory\InventoryProductFormType;
use App\Form\Inventory\InventorySupplyFormType;
use App\Form\Inventory\InventoryWasteFormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_MANAGE_INVENTORY")
 */
class InventoryController extends AbstractController
{

    /**
     * @Route("/inventory/supply/{page}", name="inventory-supply-list", requirements={"page"="\d+"})
     */
    public function listSupplyWithPagination($page = 1)
    {
        $itemsPerCategory = 0;

        $queryBuilder = $this->getDoctrine()
            ->getRepository(InventorySupply::class)
            ->findAllQueryBuilder();

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $supplies = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $supplies[] = $result;
        }

        if (!$supplies) {
//            throw $this->createNotFoundException(
//                'Nem talált egy szállítmányt sem!'
//            );
            $this->addFlash('warning', 'Nem talált egy szállítmányt sem...');
        } else {
            /**
             * Megmondom neki milyen kategoriák vannak a Supply-ban
             */
            $itemsPerCategory = [];
            foreach ($supplies as $i => $supply) {
                $supply->setProductCategories($supply->getProductCategories());
                $itemsPerCategory[$supply->getId()] = $supply->getItemCountInCategories();
            }

        }


//        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());

        return $this->render('admin/inventory/supply_list.html.twig', [
            'items' => $supplies,
            'title' => 'Bejövő áruszállítmányok listája',
            'itemsPerCategory' => $itemsPerCategory,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($supplies),
        ]);

    }

    /**
     * @Route("/inventory/supply/edit/{id}", name="inventory-supply-edit")
     */
    public function editSupply(Request $request, ?InventorySupply $supply, $id = null)
    {
        if ($supply) {
            $countItemsInSupply = $supply->countItemsInSupply();
            $itemsPerCategory = $supply->getItemCountInCategories();
            $supplyId = $supply->getId();
        } else {
            $countItemsInSupply = 0;
            $itemsPerCategory = null;
            $supplyId = null;
        }

        $categories = $this->getDoctrine()
            ->getRepository(InventoryCategory::class)
            ->findAll();
        $products = $this->getDoctrine()
            ->getRepository(InventoryProduct::class)
            ->findAllProductsAndOrderByCategory();

        if (!$supply) {
            $supply = new InventorySupply();
        }

        foreach ($products as $p => $product) {
            if (!$supply) {
                /**
                 * new Supply
                 */
                $supplyItem = new InventorySupplyItem();
                $supplyItem->setProduct($product);
                $supplyItem->setMarkup($product->getCategory()->getMarkup());
//                dump($supplyItem);die;

                $supply->addItem($supplyItem);
                $title = 'Új szállítmány rögzítése';
            } else {
                /**
                 * edit existing Supply
                 */
                $supplyItem = new InventorySupplyItem();
                $supplyItem->setProduct($product);
                $supplyItem->setMarkup($product->getCategory()->getMarkup());
//                dump($supplyItem);die;

                if ( !$supply->getProducts()->contains($product) ) {
                    $supply->addItem($supplyItem);
                } else {
                    /**
                     * A Supply első Item-jei mindig azok, amiket kiolvas az adatbázisból.
                     * Az addItem metódussal, mindig a végére pakol új Itemet.
                     * Ezért, amikor megtalálok egy terméket, ami már benne van a Supply-ban fogom és kiveszem majd visszarakom.
                     * Így, az adott Item mindig lista végére kerül, majd jöhet a következő termék.
                     */
                    $supplyItem = $supply->getItem($product);
                    $supply->removeItem($supply->getItem($product));
                    $supply->addItem($supplyItem);
                }

                $title = 'Szállítmány módosítása';
            }
        }

        $form = $this->createForm(InventorySupplyFormType::class, $supply);
        $form->handleRequest($request);
//        dump($form);die;

        if ($form->isSubmitted() && $form->isValid()) {
            $supply = $form->getData();

            /**
             * Megnézem melyik Item nem lett kitöltve és azt törlöm a Supplyból
             */
            foreach ($supply->getItems() as $i => $item) {
                if ( !$item->getQuantity() and !$item->getCog() and !$item->getRetailPrice() ) {
                    $supply->removeItem($item);
                }
            }
//            $supply->setUpdatedAt(new \DateTime('NOW'));
//            $supply->setCreatedAt(new \DateTime('NOW'));

            /**
             * Végig megyek az Itemeken és hozzájuk rendelem az aktuális Supply-t
             */
//            dump($supply->getItems()->isEmpty());die;
            if ($supply->getItems()->isEmpty()) {
                $this->addFlash('danger', 'Nem mentette el, mert egyik tételnél sem volt Mennyiség illetve Beszerzési ár sem megadva! Próbáld meg újra!');
                return $this->redirectToRoute('inventory-supply-list');
            } else {
                foreach ($supply->getItems() as $i => $item) {
                    $item->setSupply($supply);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($supply);
                $entityManager->flush();

                $this->addFlash('success', 'Szállítmány sikeresen elmentve!');

                return $this->redirectToRoute('inventory-supply-edit', ['id' => $supply->getId(),]);
            }
        }

        return $this->render('admin/inventory/supply_edit.html.twig', [
                'form' => $form->createView(),
                'title' => $title,
                'supplyId' => $supplyId,
                'categories' => $categories,
                'countItemsInSupply' => $countItemsInSupply,
                'itemsPerCategory' => $itemsPerCategory,
            ]
        );
    }

    /**
     * @Route("/inventory/waste/{page}", name="inventory-waste-list", requirements={"page"="\d+"})
     */
    public function listWasteWithPagination($page = 1)
    {
        $itemsPerCategory = 0;
        $queryBuilder = $this->getDoctrine()
            ->getRepository(InventoryWaste::class)
            ->findAllQueryBuilder();

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $wastes = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $wastes[] = $result;
        }

        if (!$wastes) {
//            throw $this->createNotFoundException(
//                'Nem talált egy számlát sem!'
//            );
            $this->addFlash('warning', 'Nem talált egy selejtet sem...');
        } else {
            /**
             * Megmondom neki milyen kategoriák vannak a Waste-ban
             */
            $itemsPerCategory = [];
            foreach ($wastes as $i => $waste) {
                $waste->setProductCategories($waste->getProductCategories());
                $itemsPerCategory[$waste->getId()] = $waste->getItemCountInCategories();
            }
        }

//        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());

        return $this->render('admin/inventory/waste_list.html.twig', [
            'items' => $wastes,
            'title' => 'Bejövő áruszállítmányok listája',
            'itemsPerCategory' => $itemsPerCategory,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($wastes),
        ]);

    }

    /**
     * The controller for creating/editing a Waste
     * Selejt kreaálás/szerkesztés
     *
     * @Route("/inventory/waste/edit/{id}", name="inventory-waste-edit")
     */
    public function editWaste(Request $request, ?InventoryWaste $waste, $id = null)
    {
        if ($waste) {
            $countItemsInWaste = $waste->countItemsInWaste();
            $itemsPerCategory = $waste->getItemCountInCategories();
            $wasteId = $waste->getId();
        } else {
            $countItemsInWaste = 0;
            $itemsPerCategory = null;
            $wasteId = null;
        }

        $categories = $this->getDoctrine()
            ->getRepository(InventoryCategory::class)
            ->findAll();
        $products = $this->getDoctrine()
            ->getRepository(InventoryProduct::class)
            ->findAllProductsAndOrderByCategory();

        if (!$waste) {
            $waste = new InventoryWaste();
        }

        foreach ($products as $p => $product) {
            if (!$waste) {
                /**
                 * new Waste
                 */
                $wasteItem = new InventoryWasteItem();
                $wasteItem->setProduct($product);
                //
                $waste->addItem($wasteItem);
                $title = 'Új adag selejt rögzítése';
            } else {
                /**
                 * edit existing Waste
                 */
                $wasteItem = new InventoryWasteItem();
                $wasteItem->setProduct($product);

                if ( !$waste->getProducts()->contains($product) ) {
                    $waste->addItem($wasteItem);
                } else {
                    /**
                     * A Waste első Item-jei mindig azok, amiket kiolvas az adatbázisból.
                     * Az addItem metódussal, mindig a végére pakol új Itemet.
                     * Ezért, amikor megtalálok egy terméket, ami már benne van a Waste-ban fogom és kiveszem majd visszarakom.
                     * Így, az adott Item mindig lista végére kerül, majd jöhet a következő termék.
                     */
                    $wasteItem = $waste->getItem($product);
                    $waste->removeItem($waste->getItem($product));
                    $waste->addItem($wasteItem);
                }

                $title = 'Selejtadatok módosítása';
            }
        }

        $form = $this->createForm(InventoryWasteFormType::class, $waste);
        $form->handleRequest($request);
//        dump($form);die;

        if ($form->isSubmitted() && $form->isValid()) {
            $waste = $form->getData();

            /**
             * Megnézem melyik Item nem lett kitöltve és azt törlöm a Wasteból
             */
            foreach ($waste->getItems() as $i => $item) {
                if ( !$item->getQuantity() ) {
                    $waste->removeItem($item);
                }
            }
//            $waste->setUpdatedAt(new \DateTime('NOW'));
//            $waste->setCreatedAt(new \DateTime('NOW'));

            /**
             * Végig megyek az Itemeken és hozzájuk rendelem az aktuális Waste-t
             */
            foreach ($waste->getItems() as $i => $item) {
                $item->setWaste($waste);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($waste);
            $entityManager->flush();

            $this->addFlash('success', 'Selejtadatok sikeresen elmentve!');

            return $this->redirectToRoute('inventory-waste-edit',['id' => $waste->getId(),]);
        }

        return $this->render('admin/inventory/waste_edit.html.twig', [
                'form' => $form->createView(),
                'title' => $title,
                'wasteId' => $wasteId,
                'categories' => $categories,
                'countItemsInWaste' => $countItemsInWaste,
                'itemsPerCategory' => $itemsPerCategory,
            ]
        );
    }

    /**
     * @Route("/inventory/product/all", name="inventory-product-list-all")
     */
    public function listAllProducts()
    {
        $items = $this->getDoctrine()
            ->getRepository(InventoryProduct::class)
            ->findAll();

        if (!$items) {
            throw $this->createNotFoundException(
                'Nem talált egy invetory productot sem! '
            );
        }

        foreach($items as $i => $item) {
            // $items[$i] is same as $item
//			$items[$i]->getDatum()->format('Y-m-d H:i:s');
        }

        return $this->render('admin/inventory/product_list.html.twig', [
            'items' => $items,
            'title' => 'Terméklista']);
    }



    /**
     * @Route("/inventory/product/edit/{id}", name="inventory-product-edit")
     */
    public function editProduct(Request $request, ?InventoryProduct $items, $id = null)
    {
        if (!$items) {
            /**
             * new Product
             */
            $form = $this->createForm(InventoryProductFormType::class);
            $title = 'Új termék rögzítése';
        } else {
            /**
             * edit existing Product
             */
//            $formFactory = $this->get('form.factory');
//            $form = $formFactory->createNamed('valami', InventoryProductFormType::class, $items);
            $form = $this->createForm(InventoryProductFormType::class, $items);
            $title = 'Termék módosítása';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $items = $form->getData();
//            $items->setUpdatedAt(new \DateTime('NOW'));
//            $items->setCreatedAt(new \DateTime('NOW'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($items);
            $entityManager->flush();

            $this->addFlash('success', 'Termékadatok sikeresen elmentve!');

            return $this->redirectToRoute('inventory-product-list-all');

        }

        return $this->render('admin/inventory/product_edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     *@ Route("/akarmi/{supply_id}/{category_id}")
     *@ ParamConverter("supply", options={"id" = "supply_id"})
     *@ ParamConverter("category", options={"id" = "category_id"})
     *
     */
    public function itemsPerCategory(bool $isWaste, ?InventorySupply $supply, ?InventoryWaste $waste, InventoryCategory $category)
    {
        $c = 0;
        if ($isWaste) {
            if (!$waste) {
                $c = null;
            } else {
                if ($waste->getProductCategories()->contains($category)) {
                    foreach ($waste->getItems() as $i => $item) {
                        if ( $category == $item->getProduct()->getCategory() ) {
                            $c += 1;
                        }
                    }
                }
            }
        } else {
            if (!$supply) {
                $c = null;
            } else {
                if ($supply->getProductCategories()->contains($category)) {
                    foreach ($supply->getItems() as $i => $item) {
                        if ( $category == $item->getProduct()->getCategory() ) {
                            $c += 1;
                        }
                    }
                }
            }
        }

        return $this->render('admin/inventory/itemsPerCategory.html.twig', [
            'itemsPerCategory' => $c,
        ]);
    }


//    /**
//     * @Route("/inventory/supply/all", name="inventory-supply-list-all")
//     */
//    public function listAllSuply()
//    {
//        $supplies = $this->getDoctrine()
//            ->getRepository(InventorySupply::class)
//            ->findAll();
//
//        /**
//         * Megmondom neki milyen kategoriák vannak a Supply-ban
//         */
//        foreach ($supplies as $i => $supply) {
//            $supply->setProductCategories($supply->getProductCategories());
//            $itemsPerCategory[$supply->getId()] = $supply->getItemCountInCategories();
//        }
////        dump($itemsPerCategory);die;
//
//        if (!$supplies) {
//            throw $this->createNotFoundException(
//                'Nem talált egy Supply-t sem! '
//            );
//        }
//
//        return $this->render('admin/inventory/supply_list.html.twig', [
//            'items' => $supplies,
//            'title' => 'Bejövő áruszállítmányok listája',
//            'itemsPerCategory' => $itemsPerCategory,
//            ]);
//    }


//    /**
//     * @Route("/inventory/waste/all", name="inventory-waste-list-all")
//     */
//    public function listAllWaste()
//    {
//        $wastes = $this->getDoctrine()
//            ->getRepository(InventoryWaste::class)
//            ->findAll();
//
//        /**
//         * Megmondom neki milyen kategoriák vannak a Waste-ban
//         */
//        foreach ($wastes as $i => $waste) {
//            $waste->setProductCategories($waste->getProductCategories());
//            $itemsPerCategory[$waste->getId()] = $waste->getItemCountInCategories();
//        }
////        dump($itemsPerCategory);die;
//
//        if (!$wastes) {
//            throw $this->createNotFoundException(
//                'Nem talált egy Waste-t sem! '
//            );
//        }
//
//        return $this->render('admin/inventory/waste_list.html.twig', [
//            'items' => $wastes,
//            'title' => 'Selejtek',
//            'itemsPerCategory' => $itemsPerCategory,
//        ]);
//    }


//    public function countItemsInCategory(InventorySupply $supply): int
//    {
//        $categories = $this->getDoctrine()
//            ->getRepository(InventoryCategory::class)
//            ->findAll();
//        foreach ($categories as $c => $category) {
//            $count = 0;
//            foreach ($supply->getProducts() as $p => $item) {
//                if ( $item->getProduct()->getCategory() == $category ) {
//                    $count += $count;
//                }
//            }
//        }
//
//        return $this->items->count();
//    }

//    /**
//     * @Route("/inventory/supply/new", name="inventory_supply_new")
//     */
//    public function newSupply(Request $request)
//    {
//
//        $products = $this->getDoctrine()
//            ->getRepository(InventoryProduct::class)
//            ->findAll();
//
//        $supply = new InventorySupply();
//
//        foreach ($products as $p => $product) {
//            $supplyItem = new InventorySupplyItem();
//            $supplyItem->setProduct($product);
////            $supplyItem->setQuantity(0);
////            $supplyItem->setCog(0);
////            $supplyItem->setMarkup(2.8);
////            $supplyItem->setRetailPrice(0);
//            $supply->addItem($supplyItem);
//        }
//
//        $form = $this->createForm(InventorySupplyFormType::class, $supply);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $supply = $form->getData();
//
//            $supply->setUpdatedAt(new \DateTime('NOW'));
//            $supply->setCreatedAt(new \DateTime('NOW'));
//
//            foreach ($supply->getItems() as $i => $item) {
//                $item->setSupply($supply);
//            }
//
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($supply);
//            $entityManager->flush();
//
//            $this->addFlash('success', 'Új áruszállítmány sikeresen rögzítve!');
//
//            return $this->redirectToRoute('inventory_product_list_all');
//        }
//
//        return $this->render('admin/inventory/supply_new.html.twig', [
//                'form' => $form->createView(),
//                'products' => $products,
//            ]
//        );
//    }



//	/**
//     * @Route("/inventory/product/new", name="inventory_product_new")
//     */
//    public function newProduct(Request $request)
//    {
//        $form = $this->createForm(InventoryProductFormType::class);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//         	$items = $form->getData();
//            $items->setUpdatedAt(new \DateTime('NOW'));
//            $items->setCreatedAt(new \DateTime('NOW'));
//
//			$entityManager = $this->getDoctrine()->getManager();
//			$entityManager->persist($items);
//			$entityManager->flush();
//
//			$this->addFlash('success', 'Új készlet tétel sikeresen rögzítve!');
//
//			return $this->redirectToRoute('inventory_product_list_all');
//        }
//
//        return $this->render('admin/inventory/product_new.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }


}