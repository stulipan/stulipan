<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a boltzaras adatbázistáblából

namespace App\Controller\Admin;

use App\Controller\Utils\GeneralUtils;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Model\DeliveryDateWithIntervals;
use App\Entity\Model\GeneratedDates;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\Order;
use App\Form\CartHiddenDeliveryDateFormType;
use App\Form\DateRangeType;
use App\Entity\DateRange;

use App\Form\OrderBillingAddressType;
use App\Form\OrderShippingAddressType;
use App\Form\OrderStatusType;
use Symfony\Component\HttpFoundation\Response;
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
//            $criteria = new \Doctrine\Common\Collections\Criteria();
//            $criteria->where($criteria->expr()->neq('status', null));
            $queryBuilder = $this->getDoctrine()->getRepository(Order::class)->findAllByQueryBuilder();
//            dd($queryBuilder);
            
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
     * @Route("/order/list/{page}/{start}/{end}", name="order-list",
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
    public function showOrderDetail(Request $request, ?Order $order, $id = null)
    {
        if (!$order) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen rendelés!' );
        }

        $isBankTransfer = $order->getPayment()->isBankTransfer() ? true : false;
        $shippingForm = $this->createForm(OrderShippingAddressType::class, $order);
        $billingForm = $this->createForm(OrderBillingAddressType::class, $order);
    
        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new \DateTime('+2 months'))->diff(new \DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[] = (new \DateTime('+'. ($i*24 + $offset).' hours'));
        }

//        dd($dates);
        $generatedDates = new GeneratedDates();
        foreach ($dates as $date) {
        
            $specialDate = $this->getDoctrine()->getRepository(DeliverySpecialDate::class)
                ->findOneBy(['specialDate' => $date]);
        
            if (!$specialDate) {
                $dateType = $this->getDoctrine()->getRepository(DeliveryDateType::class)
                    ->findOneBy(['default' => DeliveryDateType::IS_DEFAULT]);
            } else {
                $dateType = $specialDate->getDateType();
            }
            $intervals = null === $dateType ? null : $dateType->getIntervals();
        
            $dateWithIntervals = new DeliveryDateWithIntervals();
            $dateWithIntervals->setDeliveryDate($date);
            $dateWithIntervals->setIntervals($intervals);
        
            $generatedDates->addItem($dateWithIntervals);
        }
    
        $selectedDate = null === $order->getDeliveryDate() ? null : $order->getDeliveryDate();
        $selectedInterval = null === $order->getDeliveryInterval() ? null : $order->getDeliveryInterval();
        $selectedIntervalFee = null === $order->getDeliveryFee() ? null : $order->getDeliveryFee();
    
        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval, $selectedIntervalFee);
        $hiddenDateForm = $this->createForm(CartHiddenDeliveryDateFormType::class, $hiddenDates);
    
        /**
         *
         */
//        if ($order->getStatus()->getShortcode() === Order::STATUS_FULFILLED || $order->getStatus()->getShortcode() === Order::STATUS_REJECTED ||
//            $order->getStatus()->getShortcode() === Order::STATUS_RETURNED || $order->getStatus()->getShortcode() === Order::STATUS_DELETED) {
//            $isDeliveryOverdue = false;
//        } else {
        if ($order->getStatus() && $order->getStatus()->getShortcode() === Order::STATUS_CREATED) {
            $isDeliveryOverdue = $order->isDeliveryDateInPast();
        } else {
            $isDeliveryOverdue = false;
        }
        
        $statusForm = $this->createForm(OrderStatusType::class, $order);
        
        return $this->render('admin/order/order-detail.html.twig', [
            'order' => $order,
            'overdue' => true,
            'isBankTransfer' => $isBankTransfer,
            'shippingForm' => $shippingForm->createView(),
            'billingForm' => $billingForm->createView(),
            'generatedDates' => $generatedDates,
            'hiddenDateForm' => $hiddenDateForm->createView(),
            'selectedDate' => $selectedDate,
            'selectedInterval' => $selectedInterval,
            'isDeliveryOverdue' => $isDeliveryOverdue,
            'statusForm' => $statusForm->createView(),
            
        ]);
    }
    
    /**
     * Edit the shipping address information in an Order.
     * Used in AJAX.
     *
     * @Route("/order/{id}/editShippingInfo", name="order-editShippingInfo", methods={"POST"})
     */
    public function editShippingForm(Request $request, ?Order $order, $id = null) //, ValidatorInterface $validator
    {
        if (!$order) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        } else {
            $form = $this->createForm(OrderShippingAddressType::class, $order);
        }
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $order->setShippingName($data->getShippingName());
            $order->setShippingAddress($data->getShippingAddress());
            $order->setShippingPhone($data->getShippingPhone());
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();
            
            /**
             * If AJAX request, returns the list of Recipients
             */
            if ($request->isXmlHttpRequest()) {
                $this->addFlash('success', 'Címzett sikeresen módosítva!');
                $html = $this->render('admin/item.html.twig', [
                    'item' => 'Címzett sikeresen módosítva!',
                ]);
                return new Response($html, 200);
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('admin/order/shippingInfo-form.html.twig', [
                'order' => $order,
                'shippingForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
        
        /**
         * Renders form initially with data
         */
        return $this->render('admin/order/shippingInfo-form.html.twig', [
            'order' => $order,
            'shippingForm' => $form->createView(),
        ]);
    }
    
    /**
     * Edit the shipping address information in an Order.
     * Used in AJAX.
     *
     * @Route("/order/{id}/editBillingInfo", name="order-editBillingInfo", methods={"POST"})
     */
    public function editBillingForm(Request $request, ?Order $order, $id = null) //, ValidatorInterface $validator
    {
        if (!$order) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        } else {
            $form = $this->createForm(OrderBillingAddressType::class, $order);
        }
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $order->setBillingName($data->getBillingName());
            $order->setBillingCompany($data->getBillingCompany());
            $order->setBillingAddress($data->getBillingAddress());
            $order->setBillingPhone($data->getBillingPhone());
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();
            
            /**
             * If AJAX request, returns the list of Recipients
             */
            if ($request->isXmlHttpRequest()) {
                $this->addFlash('success', 'Feladó sikeresen módosítva!');
                $html = $this->render('admin/item.html.twig', [
                    'item' => 'Feladó sikeresen módosítva!',
                ]);
                return new Response($html, 200);
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         * (!?, there is a validation error)
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('admin/order/billingInfo-form.html.twig', [
                'order' => $order,
                'billingForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
        
        /**
         * Renders form initially with data
         */
        return $this->render('admin/order/billingInfo-form.html.twig', [
            'order' => $order,
            'billingForm' => $form->createView(),
        ]);
    }
    
    /**
     * Edit the delivery date information in an Order.
     * Used in AJAX.
     *
     * @Route("/order/{id}/editDeliveryDate", name="order-editDeliveryDate", methods={"POST"})
     */
    public function editDeliveryDate(Request $request, ?Order $order, $id = null, HiddenDeliveryDate $date)
    {
        if (!$order) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        } else {
            $form = $this->createForm(CartHiddenDeliveryDateFormType::class, $date);
        }
        $form->handleRequest($request);
//        dd($form->getData());
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $order->setDeliveryDate(\DateTime::createFromFormat('!Y-m-d', $data->getDeliveryDate()));
            $order->setDeliveryInterval($data->getDeliveryInterval());
            $order->setDeliveryFee($data->getDeliveryFee());
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();
        
            /**
             * If AJAX request, and because at this point the form data is processed, it returns Success (code 200)
             */
            if ($request->isXmlHttpRequest()) {
                $this->addFlash('success', 'Szállítási idő sikeresen módosítva!');
                $html = $this->render('admin/item.html.twig', [
                    'item' => 'Szállítási idő sikeresen módosítva!',
                ]);
                return new Response($html, 200);
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/hiddenDeliveryDate-form.html.twig', [
                'hiddenDateForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
    }
    
    /**
     * Edit the delivery date information in an Order.
     * Used in AJAX.
     *
     * @Route("/order/{id}/editStatus", name="order-editStatus", methods={"POST"})
     */
    public function editStatus(Request $request, ?Order $order, $id = null)
    {
        if (!$order) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        } else {
            $form = $this->createForm(OrderStatusType::class, $order);
        }
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Order $data */
            $data = $form->getData();
            $order->setStatus($data->getStatus());
        
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();
        
            /**
             * If AJAX request, and because at this point the form data is processed, it returns Success (code 200)
             */
            if ($request->isXmlHttpRequest()) {
                $this->addFlash('success', 'Rendelés állapota sikeresen módosítva! <br>Új állapot: <strong>'.$order->getStatus().'</strong>');
                $html = $this->render('admin/item.html.twig', [
                    'item' => 'Rendelés állapota sikeresen módosítva!',
                ]);
                return new Response($html, 200);
            }
        }
        /**
         * Renders form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('admin/order/status-form.html.twig', [
                'statusForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
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