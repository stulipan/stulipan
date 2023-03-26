<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Boltzaras\Controller;

use App\Services\StoreSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a InventoryInvoice entity-hez kapcsolodik es azzal dolgozik
use App\Entity\Boltzaras\InventoryInvoice;
use App\Entity\Boltzaras\InventoryInvoiceCompany;
use App\Form\Inventory\InventoryInvoiceFormType;
use App\Form\Inventory\InventoryInvoiceCompanyFormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_MANAGE_INVENTORY")
 */
class InventoryInvoiceController extends AbstractController
{

    /**
     * @Route("/invoice/delete/{id}", name="invoice-delete", methods={"DELETE"})
     */
    public function deleteInvoiceAction(InventoryInvoice $invoice)

    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $em = $this->getDoctrine()->getManager();
        $em->remove($invoice);
        $em->flush();

        return new Response(null, 204);
    }

    /**
     * @Route("/invoice/company/edit/{id}", name="invoice-company-edit")
     */
    public function editInvoiceCompany(Request $request, ?InventoryInvoiceCompany $company, $id = null)
    {
        if (!$company) {
            // new InventoryInvoice
            $form = $this->createForm(InventoryInvoiceCompanyFormType::class);
            $title = 'Új beszállító';
        } else {
            // edit existing InventoryInvoice
            $form = $this->createForm(InventoryInvoiceCompanyFormType::class, $company);
            $title = 'Beszállító módosítása';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'Beszállítói adatok sikeresen elmentve!');

            return $this->redirectToRoute('invoice-list');

        }

        return $this->render('admin/inventory/invoice-company-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
    
    /**
	 * @Route("/invoice/all", name="invoice")
	 */
	public function listActionOld()
	{
		$szamla = $this->getDoctrine()
			->getRepository(InventoryInvoice::class)
			->findAll();

		if (!$szamla) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárást sem! '
			);
		}

		// render a template, then in the template, print things with {{ szamla.munkatars }}
		foreach($szamla as $i => $item) {
			// $szamla[$i] is same as $item
			$szamla[$i]->getDatum()->format('Y-m-d H:i:s');
		}

		return $this->render('admin/inventory/invoice-list.html.twig', ['szamlak' => $szamla]);
	}


//	/**
//     * @Route("/invoice/new", name="invoice-new")
//     */
//    public function newAction(Request $request)
//    {
//        $form = $this->createForm(InventoryInvoiceFormType::class);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//         	$szamla = $form->getData();
//         	$szamla->setUpdatedAt(new \DateTime('NOW'));
//            $szamla->setCreatedAt(new \DateTime('NOW'));
//
//			$entityManager = $this->getDoctrine()->getManager();
//
//			$entityManager->persist($szamla);
//			$entityManager->flush();
//
//			$this->addFlash('success', 'Új számla sikeresen rögzítve! Jó pihenést!');
//
//			return $this->redirectToRoute('invoice-list');
//        }
//
//        return $this->render('admin/inventory/invoice-new.html.twig', [
//            'form' => $form->createView(),
//            'title' => '',
//        ]);
//    }


	/**
     * @Route("/invoice/edit/{id}", name="invoice-edit")
     */
    public function editAction(Request $request, ?InventoryInvoice $szamla, $id = null)
    {
        if (!$szamla) {
            // new InventoryInvoice
            $form = $this->createForm(InventoryInvoiceFormType::class);
            $title = 'Új számla (költség)';
        } else {
            // edit existing InventoryInvoice
            $form = $this->createForm(InventoryInvoiceFormType::class, $szamla);
            $title = 'Számla (költség) módosítása';
        }
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
         	$szamla = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($szamla);
			$entityManager->flush();
			
			$this->addFlash('success', 'Boltzárásadatok sikeresen elmentve!');
			
			return $this->redirectToRoute('invoice-list');
			
        }
        
        return $this->render('admin/inventory/invoice-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

	

//	/**
//	 * @Route("/invoice/show/{id}", name="invoice-show")
//	 */
//	public function showAction(InventoryInvoice $szamla)
//	{
//		if (!$szamla) {
//			throw $this->createNotFoundException(
//				'Nem talált egy boltzárásjelentést, ezzel az ID-vel: '.$id
//			);
//		}
//
//		$szamla->getDatum()->format('Y-m-d H:i:s');
//		$szamla->getUpdatedAt()->format('Y-m-d H:i:s');
//		return $this->render('admin/inventory/invoice-list.html.twig', ['szamla' => $szamla]);
//	}


    /**
     * @Route("/invoice/{page}", name="invoice-list", requirements={"page"="\d+"})
     */
    public function listInvoicesWithPagination($page = 1, StoreSettings $settings)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(InventoryInvoice::class)
            ->findAllQueryBuilder();

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $szamla = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $szamla[] = $result;
        }

        if (!$szamla) {
            throw $this->createNotFoundException(
                'Nem talált egy számlát sem!'
            );
        }

        foreach($szamla as $i => $item) {
            $szamla[$i]->getDatum()->format('Y-m-d H:i:s');
        }

//        $paginatedCollection = new PaginatedCollection($szamla, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ szamla.munkatars }}
        return $this->render('admin/inventory/invoice-list.html.twig', [
            'szamlak' => $szamla,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($szamla),
            'title' => 'Számlák (költségek) listája',
        ]);


    }
    
}