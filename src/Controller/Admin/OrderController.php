<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a boltzaras adatbázistáblából

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Form\BoltzarasFormType;
use App\Form\BoltzarasWebFormType;
use App\Form\DateRangeType;
use App\Entity\DateRange;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\MonologBundle\SwiftMailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Pagination\PaginatedCollection;

/**
 * @IsGranted("ROLE_MANAGE_ORDERS")
 * @Route("/admin")
 */
class OrderController extends AbstractController
{

    /**
     * @Route("/order-table/{page}/{start}/{end}", name="order-list-table",
     *     requirements={"page"="\d+"},
     *     defaults={"start"=null, "end"=null}
     *     )
     */
    public function listInTable(Request $request, $page = 1)
    {
        $start = $request->attributes->get('start');
        $end = $request->attributes->get('end');

        /**
         *  DateRange form creation
         */
        $dateRange = new DateRange();
        if ( !isset($start) or $start === null or $start == "") {
        } else {
            $dateRange->setStart(\DateTime::createFromFormat('!Y-m-d',$start));
            $start = $dateRange->getStart();
//            $dateRange->setStart(\DateTime::createFromFormat('Y-m-d h:m',$start));
        }
        if (!isset($end) or $end === null or $end == "") {
        } else {
            $dateRange->setEnd(\DateTime::createFromFormat('!Y-m-d',$end));
            $end = $dateRange->getEnd();
//            $dateRange->setEnd(\DateTime::createFromFormat('Y-m-d h:m',$end));
        }
//        $dateRangeForm = $this->createForm(DateRangeType::class, $dateRange);
        $dateRangeForm = $this->createForm(DateRangeType::class);

        if ($start and $end) {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findAllBetweenDates($start, $end);
//            $totalKeszpenzEsBankkartya = $this->getDoctrine()
//                ->getRepository(Boltzaras::class)
//                ->sumAllBetweenDates($start, $end)
//                ->getSingleResult();
//            $totalWebshopForgalom = $this->getDoctrine()
//                ->getRepository(BoltzarasWeb::class)
//                ->sumAllBetweenDates($start, $end)
//                ->getSingleResult();
        } else {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findAllQueryBuilder();
            //->findAllGreaterThanKassza(10);
//            $totalKeszpenzEsBankkartya = $this->getDoctrine()
//                ->getRepository(Boltzaras::class)
//                ->sumAllQueryBuilder()
//                ->getSingleResult();
//            $totalWebshopForgalom = $this->getDoctrine()
//                ->getRepository(BoltzarasWeb::class)
//                ->sumAllQueryBuilder()
//                ->getSingleResult();
        }

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        //On the Pagerfanta object, call setMaxPerPage() to set displayed items to 10, the set current page
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }


        $orders = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $orders[] = $result;
        }

        if (!$orders) {
            $this->addFlash('danger', 'Keresés eredménye: Nem talált boltzárást az adott idősávban!');
            return $this->redirectToRoute('order-list');
        }


        return $this->render('admin/order/order-list-table.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($orders),
            'title' => 'Rendelések',
            'dateRangeForm' => $dateRangeForm->createView(),
            'orderCount' => empty($orders) ? 'Nincsenek rendelések' : count($orders),
        ]);
    }


    /**
     * @Route("/order-table2/{page}/{start}/{end}", name="order-list-table",
     *     requirements={"page"="\d+"},
     *     defaults={"start"=null, "end"=null}
     *     )
     */
    public function listInTable2(Request $request, $page = 1)
    {
        $start = $request->attributes->get('start');
        $end = $request->attributes->get('end');

        /**
         *  DateRange form creation
         */
        $dateRange = new DateRange();
        if ( !isset($start) or $start === null or $start == "") {
        } else {
            $dateRange->setStart(\DateTime::createFromFormat('!Y-m-d',$start));
            $start = $dateRange->getStart();
//            $dateRange->setStart(\DateTime::createFromFormat('Y-m-d h:m',$start));
        }
        if (!isset($end) or $end === null or $end == "") {
        } else {
            $dateRange->setEnd(\DateTime::createFromFormat('!Y-m-d',$end));
            $end = $dateRange->getEnd();
//            $dateRange->setEnd(\DateTime::createFromFormat('Y-m-d h:m',$end));
        }
//        $dateRangeForm = $this->createForm(DateRangeType::class, $dateRange);
        $dateRangeForm = $this->createForm(DateRangeType::class);

        if ($start and $end) {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findAllBetweenDates($start, $end);
//            $totalKeszpenzEsBankkartya = $this->getDoctrine()
//                ->getRepository(Boltzaras::class)
//                ->sumAllBetweenDates($start, $end)
//                ->getSingleResult();
//            $totalWebshopForgalom = $this->getDoctrine()
//                ->getRepository(BoltzarasWeb::class)
//                ->sumAllBetweenDates($start, $end)
//                ->getSingleResult();
        } else {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findAllQueryBuilder();
            //->findAllGreaterThanKassza(10);
//            $totalKeszpenzEsBankkartya = $this->getDoctrine()
//                ->getRepository(Boltzaras::class)
//                ->sumAllQueryBuilder()
//                ->getSingleResult();
//            $totalWebshopForgalom = $this->getDoctrine()
//                ->getRepository(BoltzarasWeb::class)
//                ->sumAllQueryBuilder()
//                ->getSingleResult();
        }

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        //On the Pagerfanta object, call setMaxPerPage() to set displayed items to 10, the set current page
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }


        $orders = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $orders[] = $result;
        }

        if (!$orders) {
            $this->addFlash('danger', 'Keresés eredménye: Nem talált boltzárást az adott idősávban!');
            return $this->redirectToRoute('order-list');
        }


        return $this->render('admin/order/order-list-table-withProducts.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($orders),
            'title' => 'Rendelések',
            'dateRangeForm' => $dateRangeForm->createView(),
            'orderCount' => empty($orders) ? 'Nincsenek rendelések' : count($orders),
        ]);
    }

    /**
     * @Route("/order/{page}/{start}/{end}", name="order-list",
     *     requirements={"page"="\d+"},
     *     defaults={"start"=null, "end"=null}
     *     )
     */
    public function listActionWithPagination(Request $request, $page = 1)
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
            $dateRange->setStart(\DateTime::createFromFormat('!Y-m-d',$start));
            $start = $dateRange->getStart();
//            $dateRange->setStart(\DateTime::createFromFormat('Y-m-d h:m',$start));
        }
        if (!isset($end) or $end === null or $end == "") {
        } else {
            $dateRange->setEnd(\DateTime::createFromFormat('!Y-m-d',$end));
            $end = $dateRange->getEnd();
//            $dateRange->setEnd(\DateTime::createFromFormat('Y-m-d h:m',$end));
        }
//        $dateRangeForm = $this->createForm(DateRangeType::class, $dateRange);
        $dateRangeForm = $this->createForm(DateRangeType::class);

        if ($start and $end) {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findAllBetweenDates($start, $end);
//            $totalKeszpenzEsBankkartya = $this->getDoctrine()
//                ->getRepository(Boltzaras::class)
//                ->sumAllBetweenDates($start, $end)
//                ->getSingleResult();
//            $totalWebshopForgalom = $this->getDoctrine()
//                ->getRepository(BoltzarasWeb::class)
//                ->sumAllBetweenDates($start, $end)
//                ->getSingleResult();
        } else {
            $queryBuilder = $this->getDoctrine()
                ->getRepository(Order::class)
                ->findAllQueryBuilder();
            //->findAllGreaterThanKassza(10);
//            $totalKeszpenzEsBankkartya = $this->getDoctrine()
//                ->getRepository(Boltzaras::class)
//                ->sumAllQueryBuilder()
//                ->getSingleResult();
//            $totalWebshopForgalom = $this->getDoctrine()
//                ->getRepository(BoltzarasWeb::class)
//                ->sumAllQueryBuilder()
//                ->getSingleResult();
        }

        //Start with $adapter = new DoctrineORMAdapter() since we're using Doctrine, and pass it the query builder.
        //Next, create a $pagerfanta variable set to new Pagerfanta() and pass it the adapter.
        //On the Pagerfanta object, call setMaxPerPage() to set displayed items to 10, the set current page
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }


        $orders = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $orders[] = $result;
        }

        if (!$orders) {
//            throw $this->createNotFoundException(
//                'Nem talált egy boltzárást sem! ' );
            $this->addFlash('danger', 'Keresés eredménye: Nem talált boltzárást az adott idősávban!');
            return $this->redirectToRoute('order-list');

        }

//        foreach($orders as $i => $item) {
//            $orders[$i]->getIdopont()->format('Y-m-d H:i:s');
//            $orders[$i]->getModositasIdopontja()->format('Y-m-d H:i:s');
//        }

        $paginatedCollection = new PaginatedCollection($orders, $pagerfanta->getNbResults());

        // render a template, then in the template, print things with {{ jelentes.munkatars }}
        return $this->render('admin/order/order-list.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($orders),
            'title' => 'Rendelések',
            'dateRangeForm' => $dateRangeForm->createView(),
            'orderCount' => empty($orders) ? 'Nincsenek rendelések' : count($orders),
        ]);
    }

    /**
	 * @Route("/order/all", name="order-list_all")
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

		return $this->render('admin/order/order-list.html.twig', [
		    'jelentesek' => $jelentes,
            'title' => 'Boltzárások listája',
            'keszpenz' => $totalKeszpenzEsBankkartya['keszpenz'],
            'bankkartya' => $totalKeszpenzEsBankkartya['bankkartya'],
            ]);
	}

	public function addBoltzarasForm()
    {
        $form = $this->createForm(BoltzarasFormType::class);

        return $this->render('admin/order/_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Új boltzárás rögzítése',
        ]);
    }


	/**
     * @Route("/order/edit/{id}", name="order-edit")
     */
    public function editAction(Request $request, ?Boltzaras $boltzaras, $id = null, \Swift_Mailer $mailer)
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
            $order->setModositasIdopontja();
         	
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($boltzaras);
			$entityManager->flush();

            $subject = 'Napi boltzárás';
            $email = (new \Swift_Message())
                ->setSubject($subject)
                ->setFrom(['info@tulipanfutar.hu' => 'Difiori boltzárás'])
                ->setTo('info@difiori.hu')
                ->setBody(
                    $this->renderView('admin/emails/order-napi-riport.html.twig', [
                            'kassza' => $order->getKassza(),
                            'keszpenz' => $order->getKeszpenz(),
                            'bankkartya' => $order->getBankkartya(),
                            'munkatars' => $order->getMunkatars(),
                            'subject' => $subject,
                            'idopont' => $order->getIdopont(),
                        ]
                    ),
                    'text/html'
                );
            $mailer->send($email);

            $this->addFlash('success', 'Boltzárás sikeresen elmentve!');

			return $this->redirectToRoute('order-list');
			
        }
        
        return $this->render('admin/order/order-edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Boltzárás adatainak módosítása',
        ]);
    }

    /**
     * @Route("/order/detail/{id}", name="order-detail")
     */
    public function editBoltzarasWebshop(Request $request, ?Order $order, $id = null)
    {
        if (!$order) {
            throw $this->createNotFoundException(
                'DFR hiba: Nincs ilyen rendelés!' );
        }

//        dd($order);
        $title = 'Rendelés: #'.$order->getNumber();
        $rendeles = $this->getDoctrine()->getRepository(Order::class)
            ->findOneById($id);





        return $this->render('admin/order/order-detail.html.twig', [
            'order' => $order,
            'title' => $title,
        ]);
    }


//	/**
//	 * @Route("/order/show/{id}", name="order-show")
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
//		return $this->render('admin/order/order-list.html.twig', ['jelentes' => $jelentes]);
//	}




    //	/**
//     * @Route("/order/new", name="order-new")
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
//			//return $this->redirectToRoute('order-new');
//			return $this->redirectToRoute('order-list');
//        }
//
//        return $this->render('admin/order/order-edit.html.twig', [
//            'form' => $form->createView(),
//            'title' => 'Új boltzárás rögzítése',
//        ]);
//    }

}