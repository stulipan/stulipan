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

//az alabbibol fogja tudni hogy a Szamla entity-hez kapcsolodik es azzal dolgozik
use App\Entity\Szamla;
use App\Form\SzamlaFormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 */
class SzamlaController extends Controller
{




    /**
	 * @Route("/szamlak/all", name="szamla")
	 */
	public function listActionOld()
	{
		$szamla = $this->getDoctrine()
			->getRepository(Szamla::class)
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

		return $this->render('admin/szamla/szamla_list.html.twig', ['szamlak' => $szamla]);
	}


	/**
     * @Route("/szamlak/new", name="szamla_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(SzamlaFormType::class);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
         	
         	$szamla = $form->getData();
         	$szamla->setUpdatedAt(new \DateTime('NOW'));
            $szamla->setCreatedAt(new \DateTime('NOW'));

			$entityManager = $this->getDoctrine()->getManager();

			$entityManager->persist($szamla);
			$entityManager->flush();
			
			$this->addFlash('success', 'Új számla sikeresen rögzítve! Jó pihenést!');
            
			return $this->redirectToRoute('szamla_list');
        }
        
        return $this->render('admin/szamla/szamla_new.html.twig', array(
            													'form' => $form->createView(),
        															)
        					);
    }


	/**
     * @Route("/szamlak/edit/{id}", name="szamla_edit")
     */
    public function editAction(Request $request, Szamla $szamla)
    {
        $form = $this->createForm(SzamlaFormType::class, $szamla);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
         	$szamla = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($szamla);
			$entityManager->flush();
			
			$this->addFlash('success', 'Sikeresen módosítottad a boltzárásjelentést!');
			
			return $this->redirectToRoute('szamla_list');
			
        }
        
        return $this->render('admin/szamla/szamla_edit.html.twig', array(
            													'form' => $form->createView(),
        															)
        					);
    }

	

	/**
	 * @Route("/szamlak/show/{id}", name="szamla_show")
	 */
	public function showAction(Szamla $szamla)
	{
		if (!$szamla) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárásjelentést, ezzel az ID-vel: '.$id
			);
		}

		$szamla->getDatum()->format('Y-m-d H:i:s');
		$szamla->getUpdatedAt()->format('Y-m-d H:i:s');
		return $this->render('admin/szamla/szamla_list.html.twig', ['szamla' => $szamla]);
	}


    /**
     * @Route("/szamlak/{page}", name="szamla_list", requirements={"page"="\d+"})
     */
    public function listActionWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(Szamla::class)
            ->findAllQueryBuilder();

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


        $szamla = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $szamla[] = $result;
        }

        //dump($szamla);die;

        if (!$szamla) {
            throw $this->createNotFoundException(
                'Nem talált egy számlát sem!'
            );
        }

        foreach($szamla as $i => $item) {
            // $szamla[$i] is the same as $item
            $szamla[$i]->getDatum()->format('Y-m-d H:i:s');
            //$szamla[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
        }

        $paginatedCollection = new PaginatedCollection($szamla, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ szamla.munkatars }}
        return $this->render('admin/szamla/szamla_list.html.twig', [
            'szamlak' => $szamla,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($szamla),
        ]);


    }
    
}