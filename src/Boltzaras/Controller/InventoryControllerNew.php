<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Boltzaras\Controller;

use App\Entity\InventorySupplyItem;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a InventoryProduct entity-hez kapcsolodik es azzal dolgozik
use App\Entity\InventoryProduct;
use App\Entity\InventorySupply;
use App\Form\InventoryProductFormType;
use App\Form\InventorySupplyFormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 */
class InventoryControllerNew extends AbstractController
{
    /**
     * @Route("/inventorynew/", name="inventorynew")
     */
    public function newSupplyAction1(Request $request)
    {

        $products = $this->getDoctrine()
            ->getRepository(InventoryProduct::class)
            ->findAll();

        $supply = new InventorySupply();

        foreach ($products as $p => $product) {
            $item = new InventorySupplyItem();
            $item->setProduct($product);
//            $item->setQuantity(0);
//            $item->setCog(0);
//            $item->setMarkup(2.8);
//            $item->setRetailPrice(0);
            $supply->addItem($item);
        }

//        $supply = new InventorySupply();
//        $item1 = new InventorySupplyItem();
//        $item1->setProduct($products);
//        $item1->setQuantity(99);
//        $item1->setCog(2.8);
//        $item1->setMarkup(2.8);
//        $item1->setRetailPrice(390);
//        $supply->addItem($item1);
//        $item2 = new InventorySupplyItem();
//        $item2->setProduct($products);
//        $item2->setQuantity(123);
//        $item2->setCog(2.8);
//        $item2->setMarkup(2.8);
//        $item2->setRetailPrice(1390);
//        $supply->addItem($item2);


//        dump($supply->getItems()); die;
//        dump($supply->getItems('1')); die;

        $form = $this->createForm(InventorySupplyFormType::class, $supply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $items = $form->getData();
            $items->setUpdatedAt(new DateTime('NOW'));
            $items->setCreatedAt(new DateTime('NOW'));

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($items);
            $entityManager->flush();

            $this->addFlash('success', 'Új áruszállítmány sikeresen rögzítve!');

            return $this->redirectToRoute('inventory_product_list_all');
        }

//        dump($form->createView());die;
        return $this->render('admin/inventory/supply_new.html.twig', [
                'form' => $form->createView(),
                'products' => $products,
            ]
        );
    }


    
}