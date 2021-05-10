<?php

namespace App\Boltzaras\Controller;

use App\Entity\Boltzaras\Boltzaras;
use App\Entity\Boltzaras\BoltzarasWeb;
use App\Boltzaras\Form\BoltzarasFormType;
use App\Boltzaras\Form\BoltzarasWebFormType;
use App\Form\DateRangeType;
use App\Entity\DateRange;

use App\Services\StoreSettings;
use DateTime;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\MonologBundle\SwiftMailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Pagination\PaginatedCollection;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_MANAGE_BOLTZARAS")
 */
class BoltzarasController extends AbstractController
{

    /**
	 * @Route("/boltzaras/all", name="boltzaras_list_all")
	 */
	public function listActionOld()
	{
		$jelentes = $this->getDoctrine()
			->getRepository(Boltzaras::class)
			->findAll();
        $totalKeszpenzEsBankkartya = $this->getDoctrine()
            ->getRepository(Boltzaras::class)
            ->sumAllQueryBuilder()
            ->getSingleResult();

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

		return $this->render('admin/boltzaras/boltzaras_list.html.twig', [
		    'jelentesek' => $jelentes,
            'title' => 'Boltzárások listája',
            'keszpenz' => $totalKeszpenzEsBankkartya['keszpenz'],
            'bankkartya' => $totalKeszpenzEsBankkartya['bankkartya'],
            ]);
	}

	public function addBoltzarasForm()
    {
        $form = $this->createForm(BoltzarasFormType::class);

        return $this->render('admin/boltzaras/_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Új boltzárás rögzítése',
        ]);
    }


	/**
     * @Route("/boltzaras/edit/{id}", name="boltzaras-edit")
     */
    public function editAction(Request $request, ?Boltzaras $boltzaras, $id = null, Swift_Mailer $mailer)
    {
        if (!$boltzaras) {
            // new Boltzaras
            $form = $this->createForm(BoltzarasFormType::class);
            $title = 'Új boltzárás rögzítése';
        } else {
            // edit Boltzaras
            $form = $this->createForm(BoltzarasFormType::class, $boltzaras);
            $title = 'Boltzárás adatainak módosítása';
        }

        // handleRequest only handles data on POST
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $boltzaras = $form->getData();
            $boltzaras->setModositasIdopontja();
         	
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($boltzaras);
			$entityManager->flush();

            $subject = 'Napi boltzárás';
            $email = (new Swift_Message())
                ->setSubject($subject)
                ->setFrom(['rafinadekor@gmail.com' => 'Napi boltzárás'])
                ->setTo('rafinadekor@gmail.com')
                ->setBody(
                    $this->renderView('admin/emails/boltzaras-napi-riport.html.twig', [
                            'kassza' => $boltzaras->getKassza(),
                            'keszpenz' => $boltzaras->getKeszpenz(),
                            'bankkartya' => $boltzaras->getBankkartya(),
                            'munkatars' => $boltzaras->getMunkatars(),
                            'subject' => $subject,
                            'idopont' => $boltzaras->getIdopont(),
                        ]
                    ),
                    'text/html'
                );
            $mailer->send($email);

            $this->addFlash('success', 'Boltzárás sikeresen elmentve!');

			return $this->redirectToRoute('boltzaras_list');
			
        }
        
        return $this->render('admin/boltzaras/boltzaras_edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Boltzárás adatainak módosítása',
        ]);
    }

    /**
     * @Route("/boltzaras/webshop/edit/{id}", name="boltzaras-webshop-edit")
     */
    public function editBoltzarasWebshop(Request $request, ?BoltzarasWeb $boltzarasWeb , $id = null)
    {
        if (!$boltzarasWeb) {
            // new BoltzarasWeb
            $form = $this->createForm(BoltzarasWebFormType::class);
            $title = 'Új webes boltzárás';
        } else {
            // edit BoltzarasWeb
            $form = $this->createForm(BoltzarasWebFormType::class, $boltzarasWeb);
            $title = 'Webes boltzárás módosítása';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boltzarasWeb = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($boltzarasWeb);
            $entityManager->flush();

            $this->addFlash('success', 'Webes boltzárás sikeresen elmentve!');

            return $this->redirectToRoute('boltzaras_list');

        }

        return $this->render('admin/boltzaras/boltzaras-web_edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }


//	/**
//	 * @Route("/boltzaras/show/{id}", name="boltzaras_show")
//	 */
//	public function showAction(Boltzaras $jelentes)
//	{
//		if (!$jelentes) {
//			throw $this->createNotFoundException(
//				'Nem talált egy boltzárásjelentést, ezzel az ID-vel: '.$id
//			);
//		}
//
//       	//ezzel kiírom simán az db-ből kinyert adatokat
//		//dump($jelentes);die;
//
//		// or render a template and print things with {{ jelentes.munkatars }}
//		$jelentes->getIdopont()->format('Y-m-d H:i:s');
//		$jelentes->getModositasIdopontja()->format('Y-m-d H:i:s');
//		return $this->render('admin/boltzaras/boltzaras_list.html.twig', ['jelentes' => $jelentes]);
//	}


    /**
     * @Route("/boltzaras/{page}/{start}/{end}", name="boltzaras_list",
     *     requirements={"page"="\d+"},
     *     defaults={"start"=null, "end"=null}
     *     )
     */
    public function listActionWithPagination(Request $request, $page = 1, StoreSettings $settings)
    {
//        $this->denyAccessUnlessGranted('ROLE_USER');

        $start = $request->attributes->get('start');
        $end = $request->attributes->get('end');

        /**
         *  DateRange form creation
         */
        $dateRange = new DateRange();
        if ( !isset($start) or $start === null or $start == "") {
        } else {
            $dateRange->setStart(DateTime::createFromFormat('!Y-m-d',$start));
            $start = $dateRange->getStart();
//            $dateRange->setStart(\DateTime::createFromFormat('Y-m-d h:m',$start));
        }
        if (!isset($end) or $end === null or $end == "") {
        } else {
            $dateRange->setEnd(DateTime::createFromFormat('!Y-m-d',$end));
            $end = $dateRange->getEnd();
//            $dateRange->setEnd(\DateTime::createFromFormat('Y-m-d h:m',$end));
        }
//        $dateRangeForm = $this->createForm(DateRangeType::class, $dateRange);
        $dateRangeForm = $this->createForm(DateRangeType::class);

        if ($start and $end) {
//            dump($dateRange); die;
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Boltzaras::class)
//                ->findAllBetweenDates($dateRange->getStart(), $dateRange->getEnd());
                ->findAllBetweenDates($start, $end);
            $totalKeszpenzEsBankkartya = $this->getDoctrine()
                ->getRepository(Boltzaras::class)
                ->sumAllBetweenDates($start, $end)
                ->getSingleResult();
            $totalWebshopForgalom = $this->getDoctrine()
                ->getRepository(BoltzarasWeb::class)
                ->sumAllBetweenDates($start, $end)
                ->getSingleResult();
        } else {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Boltzaras::class)
                ->findAllQueryBuilder();
            //->findAllGreaterThanKassza(10);
            $totalKeszpenzEsBankkartya = $this->getDoctrine()
                ->getRepository(Boltzaras::class)
                ->sumAllQueryBuilder()
                ->getSingleResult();
            $totalWebshopForgalom = $this->getDoctrine()
                ->getRepository(BoltzarasWeb::class)
                ->sumAllQueryBuilder()
                ->getSingleResult();
        }

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
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

        if (!$jelentes) {
//            throw $this->createNotFoundException(
//                'Nem talált egy boltzárást sem! ' );
            $this->addFlash('danger', 'Keresés eredménye: Nem talált boltzárást az adott idősávban!');
            return $this->redirectToRoute('boltzaras_list');

        }

        foreach($jelentes as $i => $item) {
            // $jelentes[$i] is the same as $item
            $jelentes[$i]->getIdopont()->format('Y-m-d H:i:s');
            $jelentes[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
        }

//        $paginatedCollection = new PaginatedCollection($jelentes, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ jelentes.munkatars }}
        return $this->render('admin/boltzaras/boltzaras_list.html.twig', [
            'jelentesek' => $jelentes,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($jelentes),
            'title' => 'Boltzárások listája',
            'dateRangeForm' => $dateRangeForm->createView(),
            'keszpenz' => $totalKeszpenzEsBankkartya['keszpenz'],
            'bankkartya' => $totalKeszpenzEsBankkartya['bankkartya'],
            'kassza' => $totalKeszpenzEsBankkartya['kassza'],
            'webshop' => $totalWebshopForgalom['webshopforgalom'],
        ]);
    }

    //	/**
//     * @Route("/boltzaras/new", name="boltzaras_new")
//     */
//    public function newAction(Request $request)
//    {
//        $form = $this->createForm(BoltzarasFormType::class);
//
//        // handleRequest only handles data on POST
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//         	//ezzel kiirom siman az POST-al submitolt adatokat
//         	//dump($form->getData());die;
//
//         	$zarasAdatok = $form->getData();
//         	$zarasAdatok->setModositasIdopontja();
//
//			// you can fetch the EntityManager via $this->getDoctrine()
//			// or you can add an argument to your action: index(EntityManagerInterface $entityManager)
//			$entityManager = $this->getDoctrine()->getManager();
//
//			// tell Doctrine you want to (eventually) save the Product (no queries yet)
//			$entityManager->persist($zarasAdatok);
//
//			// actually executes the queries (i.e. the INSERT query)
//			$entityManager->flush();
//
//			$this->addFlash('success', 'Sikeresen leadtad a boltzárásjelentést! Jó pihenést!');
//
//			//return $this->redirectToRoute('boltzaras_new');
//			return $this->redirectToRoute('boltzaras_list');
//        }
//
//        return $this->render('admin/boltzaras/boltzaras_edit.html.twig', [
//            'form' => $form->createView(),
//            'title' => 'Új boltzárás rögzítése',
//        ]);
//    }

}