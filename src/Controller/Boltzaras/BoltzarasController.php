<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a boltzaras adatbázistáblából

namespace App\Controller\Boltzaras;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

//az alabbibol fogja tudni hogy a Boltzaras entity-hez kapcsolodik es azzal dolgozik
use App\Entity\Boltzaras;
use App\Form\BoltzarasFormType;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 */
class BoltzarasController extends Controller
{




    /**
	 * @Route("/boltzaras/all", name="boltzaras_list_all")
	 */
	public function listActionOld()
	{
		$jelentes = $this->getDoctrine()
			->getRepository(Boltzaras::class)
			->findAll();

		if (!$jelentes) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárást sem! '
			);
		}

		// render a template, then in the template, print things with {{ jelentes.munkatars }}
		foreach($jelentes as $i => $item) {
			// $jelentes[$i] is same as $item
			$jelentes[$i]->getIdopont()->format('Y-m-d H:i:s');
			$jelentes[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
		}

		return $this->render('admin/boltzaras/boltzaras_list.html.twig', ['jelentesek' => $jelentes]);
	}


	/**
     * @Route("/boltzaras/new", name="boltzaras_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(BoltzarasFormType::class);
        
        // handleRequest only handles data on POST
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
         	//ezzel kiirom siman az POST-al submitolt adatokat
         	//dump($form->getData());die;
         	
         	$zarasAdatok = $form->getData();
         	$zarasAdatok->setModositasIdopontja(); 

			// you can fetch the EntityManager via $this->getDoctrine()
			// or you can add an argument to your action: index(EntityManagerInterface $entityManager)
			$entityManager = $this->getDoctrine()->getManager();

			// tell Doctrine you want to (eventually) save the Product (no queries yet)
			$entityManager->persist($zarasAdatok);

			// actually executes the queries (i.e. the INSERT query)
			$entityManager->flush();
			
			$this->addFlash('livSuccess', 'Sikeresen leadtad a boltzárásjelentést! Jó pihenést!');
			
			//return $this->redirectToRoute('boltzaras_new');
			return $this->redirectToRoute('boltzaras_list');
        }
        
        return $this->render('admin/boltzaras/boltzaras_new.html.twig', array(
            													'form' => $form->createView(),
        															)
        					);
    }


	/**
     * @Route("/boltzaras/edit/{id}", name="boltzaras_edit")
     */
    public function editAction(Request $request, Boltzaras $zarasAdatok)
    {
    	//itt instanszolja a Jelentes formot, amit a $zarasAdatokkal populálja
    	//lásd a második paramétert, ami megmondja a formnak milyen adatokat szórjon bele
        $form = $this->createForm(BoltzarasFormType::class, $zarasAdatok);
        
        // handleRequest only handles data on POST
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
         	//ezzel kiirom siman az POST-al submitolt adatokat
         	//dump($form->getData());die;
         	
         	$zarasAdatok = $form->getData();
         	$zarasAdatok->setModositasIdopontja(); 
         	
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($zarasAdatok);
			$entityManager->flush();
			
			$this->addFlash('livSuccess', 'Sikeresen módosítottad a boltzárásjelentést!');
			
			return $this->redirectToRoute('boltzaras_list');
			
        }
        
        return $this->render('admin/boltzaras/boltzaras_edit.html.twig', array(
            													'form' => $form->createView(),
        															)
        					);
    }

	

	/**
	 * @Route("/boltzaras/show/{id}", name="boltzaras_show")
	 */
	public function showAction(Boltzaras $jelentes)
	{
		if (!$jelentes) {
			throw $this->createNotFoundException(
				'Nem talált egy boltzárásjelentést, ezzel az ID-vel: '.$id
			);
		}

       	//ezzel kiírom simán az db-ből kinyert adatokat
		//dump($jelentes);die; 

		// or render a template and print things with {{ jelentes.munkatars }}
		$jelentes->getIdopont()->format('Y-m-d H:i:s');
		$jelentes->getModositasIdopontja()->format('Y-m-d H:i:s');
		return $this->render('admin/boltzaras/boltzaras_list.html.twig', ['jelentes' => $jelentes]);
	}


    /**
     * @Route("/boltzaras/{page}", name="boltzaras_list", requirements={"page"="\d+"})
     */
    public function listActionWithPagination($page = 1)
    {
        $queryBuilder = $this->getDoctrine()
            ->getRepository(Boltzaras::class)
            ->findAllQueryBuilder();
        //->findAllGreaterThanKassza(10);

        //dump($page); die;

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


        $jelentes = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $jelentes[] = $result;
        }

        //dump($jelentes);die;

        if (!$jelentes) {
            throw $this->createNotFoundException(
                'Nem talált egy boltzárást sem! '
            );
        }

        foreach($jelentes as $i => $item) {
            // $jelentes[$i] is the same as $item
            $jelentes[$i]->getIdopont()->format('Y-m-d H:i:s');
            $jelentes[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
        }

        $paginatedCollection = new PaginatedCollection($jelentes, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ jelentes.munkatars }}
        return $this->render('admin/boltzaras/boltzaras_list.html.twig', [
            'jelentesek' => $jelentes,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($jelentes),
        ]);


    }



    /**
     * @Route("/napizaras/new", name="napizaras_new")
     */
    public function index()
    {
        // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to your action: index(EntityManagerInterface $entityManager)
        $entityManager = $this->getDoctrine()->getManager();

        //itt instaszoljuk az entity-t
        $riport = new Boltzaras();   
        $riport->setIdopont();
        $riport->setModositasIdopontja();
        $riport->setKassza(149990.00);
        $riport->setBankkartya(149990.00);
        $riport->setKeszpenz(149990.00);        
        $riport->setMunkatars('2');

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($riport);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('<html><body>Saved new Napizaras with id '.$riport->getID().' | '.$riport
                ->getIdopont()->format('Y-m-d H:i:s').' | Kassza: '.$riport->getKassza().'</body></html>');
    }
}