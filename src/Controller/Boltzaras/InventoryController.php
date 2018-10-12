<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Controller\Boltzaras;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a InventoryProduct entity-hez kapcsolodik es azzal dolgozik
use App\Entity\InventoryProduct;
use App\Repository\InventoryProductRepository;
use App\Entity\InventoryCategory;
use App\Entity\InventorySupply;
use App\Entity\InventorySupplyItem;
use App\Form\InventoryProductFormType;
use App\Form\InventorySupplyFormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 */
class InventoryController extends Controller
{

    /**
     * @Route("/inventory/supply/all", name="inventory-supply-list-all")
     */
    public function listAllSuply()
    {
        $items = $this->getDoctrine()
            ->getRepository(InventorySupply::class)
            ->findAll();

        if (!$items) {
            throw $this->createNotFoundException(
                'Nem talált egy Supply-t sem! '
            );
        }

        return $this->render('admin/inventory/supply_list.html.twig', ['items' => $items]);
    }

    /**
     * @Route("/inventory/supply/edit/{id}", name="inventory-supply-edit")
     */
    public function editSupply(Request $request, ?InventorySupply $supply, $id = null)
    {
        $categories = $this->getDoctrine()
            ->getRepository(InventoryCategory::class)
            ->findAll();

        if (!$supply) {
            /**
             * new Supply
             */
            $products = $this->getDoctrine()
                ->getRepository(InventoryProduct::class)
                ->findAllProductsAndOrderByCategory();

            $supply = new InventorySupply();

            foreach ($products as $p => $product) {
                $supplyItem = new InventorySupplyItem();
                $supplyItem->setProduct($product);
                $supply->addItem($supplyItem);
            }
            $title = 'Új szállítmány rögzítése';
        } else {
            /**
             * edit existing Supply
             */
            $title = 'Szállítmány módosítása';
        }

        $form = $this->createForm(InventorySupplyFormType::class, $supply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supply = $form->getData();

            /**
             * Megnésem melyik Item nem lett kitöltve és azt törlöm a Supplyból
             */
            foreach ($supply->getItems() as $i => $item) {
                if ( !$item->getQuantity() and !$item->getCog() and !$item->getMarkup() and !$item->getRetailPrice() ) {
                    $supply->removeItem($item);
                }
            }

            $supply->setUpdatedAt(new \DateTime('NOW'));
            $supply->setCreatedAt(new \DateTime('NOW'));

            /**
             * Végig megyek az Itemeken és hozzájuk rendelem az aktuális Supply-t
             */
            foreach ($supply->getItems() as $i => $item) {
                $item->setSupply($supply);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supply);
            $entityManager->flush();

            $this->addFlash('success', 'Szállítmány sikeresen elmentve!');

            return $this->redirectToRoute('inventory-supply-list-all');
        }

        return $this->render('admin/inventory/supply_edit.html.twig', [
                'form' => $form->createView(),
                'title' => $title,
                'categories' => $categories,
            ]
        );
    }

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

		return $this->render('admin/inventory/product_list.html.twig', ['items' => $items]);
	}


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


	/**
     * @Route("/inventory/product/edit/{id}", name="inventory-product-edit")
     */
    public function editProduct(Request $request, ?InventoryProduct $items, $id = null)
    {
        if (!$items) {
            // new Product
            $form = $this->createForm(InventoryProductFormType::class);
            $title = 'Új termék rögzítése';
        } else {
            // edit existing Product
            $form = $this->createForm(InventoryProductFormType::class, $items);
            $title = 'Termék módosítása';
        }

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $items = $form->getData();
            $items->setUpdatedAt(new \DateTime('NOW'));
            $items->setCreatedAt(new \DateTime('NOW'));

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
	 * @Route("/inventory/product/show/{id}", name="inventory-product-show")
	 */
	public function showAction(InventoryProduct $items)
	{
		if (!$items) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárásjelentést, ezzel az ID-vel: '.$id
			);
		}

        $items->getDatum()->format('Y-m-d H:i:s');
        $items->getUpdatedAt()->format('Y-m-d H:i:s');
		return $this->render('admin/inventory/product_list.html.twig', ['item' => $items]);
	}


    /**
     * @Route("/inventory/product/{page}", name="inventory-product-list", requirements={"page"="\d+"})
     */
    public function listActionWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(InventoryProduct::class)
            ->findAll();
//            ->findAllQueryBuilder();

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        //On the Pagerfanta object, call setMaxPerPage() to set displayed items to 10, the set current page
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }


        $items = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $items[] = $result;
        }

        if (!$items) {
            throw $this->createNotFoundException(
                'Nem talált egy számlát sem!'
            );
        }

        foreach($items as $i => $item) {
            // $items[$i] is the same as $item
            $items[$i]->getDatum()->format('Y-m-d H:i:s');
            //$items[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
        }

        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ szamla.munkatars }}
        return $this->render('admin/inventory/product_list.html.twig', [
            'items' => $items,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($items),
        ]);


    }
    
}