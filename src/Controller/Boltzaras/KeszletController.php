<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Controller\Boltzaras;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a Keszlet1 entity-hez kapcsolodik es azzal dolgozik
use App\Entity\Keszlet1;
use App\Form\Keszlet1FormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 */
class KeszletController extends Controller
{

    /**
	 * @Route("/keszlet/all", name="keszlet_list_all")
	 */
	public function listAllAction()
	{
		$keszlet = $this->getDoctrine()
			->getRepository(Keszlet1::class)
			->findAll();

		if (!$keszlet) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárást sem! '
			);
		}

		// render a template, then in the template, print things with {{ szamla.munkatars }}
		foreach($keszlet as $i => $item) {
			// $keszlet[$i] is same as $item
			$keszlet[$i]->getDatum()->format('Y-m-d H:i:s');
		}

		return $this->render('admin/keszlet/keszlet_list.html.twig', ['keszlet' => $keszlet]);
	}


	/**
     * @Route("/keszlet/new", name="keszlet_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(Keszlet1FormType::class);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
         	
         	$keszlet = $form->getData();
            $keszlet->setUpdatedAt(new \DateTime('NOW'));
            $keszlet->setCreatedAt(new \DateTime('NOW'));

			$entityManager = $this->getDoctrine()->getManager();

			$entityManager->persist($keszlet);
			$entityManager->flush();
			
			$this->addFlash('success', 'Új készlet tétel sikeresen rögzítve!');
            
			return $this->redirectToRoute('keszlet_list_all');
        }
        
        return $this->render('admin/keszlet/keszlet_new.html.twig', array(
            													'form' => $form->createView(),
        															)
        					);
    }


	/**
     * @Route("/keszlet/edit/{id}", name="keszlet_edit")
     */
    public function editAction(Request $request, Keszlet1 $keszlet)
    {
        $form = $this->createForm(Keszlet1FormType::class, $keszlet);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $keszlet = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($keszlet);
			$entityManager->flush();
			
			$this->addFlash('success', 'Sikeresen módosítottad ezt a tételt!');
			
			return $this->redirectToRoute('keszlet_list_all');
			
        }
        
        return $this->render('admin/keszlet/keszlet_edit.html.twig', array(
            													'form' => $form->createView(),
        															)
        					);
    }

	

	/**
	 * @Route("/keszlet/show/{id}", name="keszlet_show")
	 */
	public function showAction(Keszlet1 $keszlet)
	{
		if (!$keszlet) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárásjelentést, ezzel az ID-vel: '.$id
			);
		}

        $keszlet->getDatum()->format('Y-m-d H:i:s');
        $keszlet->getUpdatedAt()->format('Y-m-d H:i:s');
		return $this->render('admin/keszlet/keszlet_list.html.twig', ['tetel' => $keszlet]);
	}


    /**
     * @Route("/keszlet/{page}", name="keszlet_list", requirements={"page"="\d+"})
     */
    public function listActionWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(Keszlet1::class)
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


        $keszlet = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $keszlet[] = $result;
        }

        //dump($keszlet);die;

        if (!$keszlet) {
            throw $this->createNotFoundException(
                'Nem talált egy számlát sem!'
            );
        }

        foreach($keszlet as $i => $item) {
            // $keszlet[$i] is the same as $item
            $keszlet[$i]->getDatum()->format('Y-m-d H:i:s');
            //$keszlet[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
        }

        $paginatedCollection = new PaginatedCollection($keszlet, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ szamla.munkatars }}
        return $this->render('admin/keszlet/keszlet_list.html.twig', [
            'keszlet' => $keszlet,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($keszlet),
        ]);


    }
    
}