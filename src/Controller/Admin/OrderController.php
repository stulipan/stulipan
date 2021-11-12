<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a boltzaras adatbázistáblából

namespace App\Controller\Admin;

use App\Controller\Utils\GeneralUtils;
use App\Entity\DeliveryDateType;
use App\Entity\DeliverySpecialDate;
use App\Entity\Events;
use App\Entity\Locales;
use App\Entity\Localization;
use App\Entity\Model\DeliveryDateWithIntervals;
use App\Entity\Model\GeneratedDates;
use App\Entity\Model\HiddenDeliveryDate;
use App\Entity\Order;
use App\Entity\OrderLog;
use App\Entity\OrderLogChannel;
use App\Entity\OrderStatus;
use App\Entity\PaymentTransaction;
use App\Entity\StoreEmailTemplate;
use App\Entity\User;
use App\Event\OrderEvent;
use App\Entity\PaymentStatus;
use App\Form\DeliveryDate\CartHiddenDeliveryDateFormType;
use App\Form\DateRangeType;
use App\Entity\DateRange;

use App\Form\OrderBillingAddressType;
use App\Form\OrderCommentType;
use App\Form\OrderFilterType;
use App\Form\OrderShippingAddressType;
use App\Form\OrderStatusType;
use App\Form\PaymentStatusType;
use App\Services\AdminSettings;
use App\Services\EmailSender;
use App\Services\PaymentBuilder;
use App\Services\StoreSettings;
use App\Twig\AppExtension;
use BarionClient;
use BarionEnvironment;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\MonologBundle\SwiftMailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Pagination\PaginatedCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @IsGranted("ROLE_MANAGE_ORDERS")
 * @Route("/admin")
 */
class OrderController extends AbstractController
{
    private $em;
    private $translator;
    private $twig;
    private $dispatcher;
    private $storeSettings;
    private $paymentBuilder;
    private $emailSender;

    public function __construct(TranslatorInterface $translator, Environment $twig, EventDispatcherInterface $dispatcher,
                                StoreSettings $storeSettings, PaymentBuilder $paymentBuilder, EmailSender $emailSender,
                                EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->dispatcher = $dispatcher;
        $this->storeSettings = $storeSettings;
        $this->paymentBuilder = $paymentBuilder;
        $this->emailSender = $emailSender;
    }


    /**
     * Route("/order-table2/{orderStatus}-{paymentStatus}/{page}/{start}/{end}", name="order-list-table",
     * ==== Az alabbi resz akkor kell, ha a route-be adjuk at a parametereket ====
     * @ Route("/order-table2/{orderStatus}-{paymentStatus}/{dateRange}", name="order-list-table",
     *     defaults={
     *          "paymentStatus"=null,
     *          "orderStatus"=null,
     *          "dateRange"=null,
     *          "page"=1,
     *         }
     * ===========================================================================
     * @Route("/orders", name="order-list-table",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function listOrders(Request $request, $page = 1, AdminSettings $settings) //, $dateRange = null, $paymentStatus = null, $orderStatus = null
    {
        $dateRange = $request->query->get('dateRange');
        $searchTerm = $request->query->get('searchTerm');
        $paymentStatus = $request->query->get('paymentStatus');
        $orderStatus = $request->query->get('orderStatus');
        $isCanceled = $request->query->get('isCanceled');
        $page = $request->query->get('page') ? $request->query->get('page') : $page;
//        dd($request->attributes->get('_route_params'));
//        dd($request->query->all());

        $filterTags = [];
        $urlParams = [];
        $data = $filterTags;
        $em = $this->getDoctrine();
        if ($dateRange) {
            $filterTags['dateRange'] = 'Idősáv: '.$dateRange;
            $data['dateRange'] = $dateRange;
            $urlParams['dateRange'] = $dateRange;
        }
        if ($searchTerm) {
            $filterTags['searchTerm'] = 'Keresés: '.$searchTerm;
            $data['searchTerm'] = $searchTerm;
            $urlParams['searchTerm'] = $searchTerm;
        }
        if ($orderStatus) {
            $filterTags['orderStatus'] = $em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus])->getName();
            $urlParams['orderStatus'] = $orderStatus;
            $data['orderStatus'] = $em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);
        }
        if ($paymentStatus) {
            $filterTags['paymentStatus'] = $em->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus])->getName();
            $urlParams['paymentStatus'] = $paymentStatus;
            $data['paymentStatus'] = $em->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);
        }
        if (isset($isCanceled)) {
            $filterTags['isCanceled'] = $isCanceled == 'yes' ? 'Törölt rendelések': 'Nyitott rendelések';
            $urlParams['isCanceled'] = $isCanceled;
            $data['isCanceled'] = $isCanceled;
        }

        $filterForm = $this->createForm(OrderFilterType::class, $data);
        $filterFormSidebar = $this->createForm(OrderFilterType::class, $data);

        $filterUrls = [];
        foreach ($filterTags as $key => $value) {
            // remove the current filter from the urlParams
            $shortlist = array_diff_key($urlParams,[$key => '']);

            // generate the URL with the remaining filters
            $filterUrls[$key] = $this->generateUrl('order-list-table',[
                'dateRange' => isset($shortlist['dateRange']) ? $shortlist['dateRange'] : null,
                'searchTerm' => isset($shortlist['searchTerm']) ? $shortlist['searchTerm'] : null,
                'orderStatus' => isset($shortlist['orderStatus']) ? $shortlist['orderStatus'] : null,
                'paymentStatus' => isset($shortlist['paymentStatus']) ? $shortlist['paymentStatus'] : null,
                'isCanceled' => isset($shortlist['isCanceled']) ? $shortlist['isCanceled'] : null,
            ]);
        }

        $filterQuickLinks['all'] = [
            'name' => 'Összes rendelés',
            'url' => $this->generateUrl('order-list-table'),
            'active' => false,
        ];
        $filterQuickLinks['unpaid'] = [
            'name' => 'Fizetésre váró',
            'url' => $this->generateUrl('order-list-table',['paymentStatus' => PaymentStatus::STATUS_PENDING]),
            'active' => false,
        ];
        $filterQuickLinks['unfulfilled'] = [
            'name' => 'Feldolgozás alatt',
            'url' => $this->generateUrl('order-list-table',['orderStatus' => OrderStatus::ORDER_CREATED]),
            'active' => false,
        ];

        // Generate the quicklinks which are placed above the filter
        $hasCustomFilter = false;
        if (!$dateRange && !$orderStatus && !$paymentStatus && !$searchTerm) {
            $filterQuickLinks['all']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $paymentStatus && ( $paymentStatus === PaymentStatus::STATUS_PENDING || $paymentStatus === PaymentStatus::STATUS_PARTIALLY_PAID)) {
            $filterQuickLinks['unpaid']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $orderStatus && $orderStatus === OrderStatus::ORDER_CREATED) {
            $filterQuickLinks['unfulfilled']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$hasCustomFilter) {
            $filterQuickLinks['custom'] = [
                'name' => 'Egyedi szűrés',
                'url' => $this->generateUrl('order-list-table',$request->query->all()),
                'active' => true,
            ];
        }

        $queryBuilder = $em->getRepository(Order::class)->findAllQuery([
            'dateRange' => $dateRange,
            'searchTerm' => $searchTerm,
            'orderStatus' => $orderStatus,
            'paymentStatus' => $paymentStatus,
            'isCanceled' => $isCanceled,
        ]);

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
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

//        if (!$orders) {
//            $this->addFlash('danger', 'Nem talált rendeléseket! Próbáld módosítani a szűrőket.');
//        }

        return $this->render('admin/order/order-list.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($orders),
            'orderCount' => empty($orders) ? 'Nincsenek rendelések' : count($orders),
            'filterQuickLinks' => $filterQuickLinks,
            'filterForm' => $filterForm->createView(),
            'filterTags' => $filterTags,
            'filterUrls' => $filterUrls,
            'filterFormSidebar' => $filterFormSidebar->createView(),
        ]);
    }

    /**
     * @Route("/orders/abandoned", name="order-list-abandoned",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function listAbandonedOrders(Request $request, $page = 1, AdminSettings $settings)
    {
        $dateRange = $request->query->get('dateRange');
        $searchTerm = $request->query->get('searchTerm');
        $paymentStatus = $request->query->get('paymentStatus');
        $orderStatus = $request->query->get('orderStatus');
        $page = $request->query->get('page') ? $request->query->get('page') : $page;

        $filterTags = [];
        $urlParams = [];
        $data = $filterTags;
        $em = $this->getDoctrine();
        if ($dateRange) {
            $filterTags['dateRange'] = 'Idősáv: '.$dateRange;
            $data['dateRange'] = $dateRange;
            $urlParams['dateRange'] = $dateRange;
        }
        if ($searchTerm) {
            $filterTags['searchTerm'] = 'Keresés: '.$searchTerm;
            $data['searchTerm'] = $searchTerm;
            $urlParams['searchTerm'] = $searchTerm;
        }
        if ($orderStatus) {
            $filterTags['orderStatus'] = $em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus])->getName();
            $urlParams['orderStatus'] = $orderStatus;
            $data['orderStatus'] = $em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => $orderStatus]);
        }
        if ($paymentStatus) {
            $filterTags['paymentStatus'] = $em->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus])->getName();
            $urlParams['paymentStatus'] = $paymentStatus;
            $data['paymentStatus'] = $em->getRepository(PaymentStatus::class)->findOneBy(['shortcode' => $paymentStatus]);
        }
        $filterForm = $this->createForm(OrderFilterType::class, $data);
        $filterFormSidebar = $this->createForm(OrderFilterType::class, $data);

        $filterUrls = [];
        foreach ($filterTags as $key => $value) {
            // remove the current filter from the urlParams
            $shortlist = array_diff_key($urlParams,[$key => '']);

            // generate the URL with the remaining filters
            $filterUrls[$key] = $this->generateUrl('order-list-table',[
                'dateRange' => isset($shortlist['dateRange']) ? $shortlist['dateRange'] : null,
                'searchTerm' => isset($shortlist['searchTerm']) ? $shortlist['searchTerm'] : null,
                'orderStatus' => isset($shortlist['orderStatus']) ? $shortlist['orderStatus'] : null,
                'paymentStatus' => isset($shortlist['paymentStatus']) ? $shortlist['paymentStatus'] : null,
            ]);
        }

        $filterQuickLinks['all'] = [
            'name' => 'Összes rendelés',
            'url' => $this->generateUrl('order-list-table'),
            'active' => false,
        ];
        $filterQuickLinks['unpaid'] = [
            'name' => 'Fizetésre váró',
            'url' => $this->generateUrl('order-list-table',['paymentStatus' => PaymentStatus::STATUS_PENDING]),
            'active' => false,
        ];
        $filterQuickLinks['unfulfilled'] = [
            'name' => 'Feldolgozás alatt',
            'url' => $this->generateUrl('order-list-table',['orderStatus' => OrderStatus::ORDER_CREATED]),
            'active' => false,
        ];

        // Generate the quicklinks which are placed above the filter
        $hasCustomFilter = false;
        if (!$dateRange && !$orderStatus && !$paymentStatus && !$searchTerm) {
            $filterQuickLinks['all']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $paymentStatus && ( $paymentStatus === PaymentStatus::STATUS_PENDING || $paymentStatus === PaymentStatus::STATUS_PARTIALLY_PAID)) {
            $filterQuickLinks['unpaid']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $orderStatus && $orderStatus === OrderStatus::ORDER_CREATED) {
            $filterQuickLinks['unfulfilled']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$hasCustomFilter) {
            $filterQuickLinks['custom'] = [
                'name' => 'Egyedi szűrés',
                'url' => $this->generateUrl('order-list-table',$request->query->all()),
                'active' => true,
            ];
        }

        $queryBuilder = $em->getRepository(Order::class)->findAllQuery([
            'dateRange' => $dateRange,
            'searchTerm' => $searchTerm,
            'orderStatus' => $orderStatus,
            'paymentStatus' => $paymentStatus,
        ], false);

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
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

        return $this->render('admin/order/order-list-abandoned.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($orders),
            'orderCount' => empty($orders) ? 'Nincsenek rendelések' : count($orders),
            'filterQuickLinks' => $filterQuickLinks,
            'filterForm' => $filterForm->createView(),
            'filterTags' => $filterTags,
            'filterUrls' => $filterUrls,
            'filterFormSidebar' => $filterFormSidebar->createView(),
        ]);
    }

    /**
     * @Route("/orders/filter", name="order-list-filter")
     */
    public function handleFilterForm(Request $request)
    {
        $dataFromRequest = $request->request->all();
        $formName = array_keys($dataFromRequest)[0];

        // Since there are two Filter forms on the page with randomly created unique names (blockPrefixes -> see OrderFilterType)
        // the form name must be extracted from the Request data and used to recreate the very same form.
        $form = $this->get('form.factory')->createNamed($formName, OrderFilterType::class);
//        $form = $this->createForm(OrderFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $dateRange = null;
            $searchTerm = null;
            $orderStatus = null;
            $paymentStatus = null;
            $isCanceled = null;

            if ($filters['dateRange']) {
                $dateRange = $filters['dateRange'];
            }
            if ($filters['searchTerm']) {
                $searchTerm = $filters['searchTerm'];
            }
            if ($filters['orderStatus']) {
                $orderStatus = $this->getDoctrine()->getRepository(OrderStatus::class)->find($filters['orderStatus'])->getShortcode();
            }
            if ($filters['paymentStatus']) {
                $paymentStatus = $this->getDoctrine()->getRepository(PaymentStatus::class)->find($filters['paymentStatus'])->getShortcode();
            }
            if (isset($filters['isCanceled'])) {
                $isCanceled = $filters['isCanceled'];
            }

            return $this->redirectToRoute('order-list-table',[
                'dateRange' => $dateRange,
                'searchTerm' => $searchTerm,
                'orderStatus' => $orderStatus,
                'paymentStatus' => $paymentStatus,
                'isCanceled' => $isCanceled,
            ]);
        }
        return $this->redirectToRoute('order-list-table');
    }

    /**
     * @Route("/orders/{id}", name="order-detail")
     */
    public function showOrderDetail(Request $request, ?Order $order, $id = null, \App\Services\Localization $localization)
    {
        if (!$order) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen rendelés!' );
        }

        $shippingForm = $this->createForm(OrderShippingAddressType::class, $order);
        $billingForm = $this->createForm(OrderBillingAddressType::class, $order);
    
        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new DateTime('+2 months'))->diff(new DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[] = (new DateTime('+'. ($i*24 + $offset).' hours'));
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
        $selectedIntervalFee = null === $order->getShippingFee() ? null : $order->getShippingFee();
    
        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval, $selectedIntervalFee);
        $hiddenDateForm = $this->createForm(CartHiddenDeliveryDateFormType::class, $hiddenDates);
    
        /**
         *
         */
//        if ($order->getStatus()->getShortcode() === OrderStatus::STATUS_FULFILLED || $order->getStatus()->getShortcode() === OrderStatus::ORDER_REJECTED ||
//            $order->getStatus()->getShortcode() === OrderStatus::STATUS_RETURNED || $order->getStatus()->getShortcode() === OrderStatus::ORDER_CANCELED) {
//            $isDeliveryOverdue = false;
//        } else {
        
        $statusForm = $this->createForm(OrderStatusType::class, $order);
        $paymentStatusForm = $this->createForm(PaymentStatusType::class, $order);

        $log = new OrderLog();
        $log->setChannel($this->getDoctrine()->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => OrderLog::CHANNEL_ADMIN]));
        $log->setOrder($order);
        $log->setComment(true);
//        $orderLog->setMessage('');
        $commentForm = $this->createForm(OrderCommentType::class, $log);

        return $this->render('admin/order/order-detail.html.twig', [
            'order' => $order,
            'recipientForm' => $shippingForm->createView(),
            'senderForm' => $billingForm->createView(),
            'generatedDates' => $generatedDates,
            'hiddenDateForm' => $hiddenDateForm->createView(),
            'selectedDate' => $selectedDate,
            'selectedInterval' => $selectedInterval,
            'statusForm' => $statusForm->createView(),
            'paymentStatusForm' => $paymentStatusForm->createView(),
            'commentForm' => $commentForm->createView(),
            
        ]);
    }

    /**
     * @Route("/orders/{id}/sendEmail-orderConfirmation", name="order-sendEmail-orderConfirmation")
     */
    public function sendOrderConfirmation(Request $request, ?Order $order, $id = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /orders/send-confirmation/{id}');
        }

        if (!$order) {
            $json = json_encode(['error' => 'Nincs ilyen rendelés!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        try {
            $this->emailSender->sendEmail($order, StoreEmailTemplate::ORDER_CONFIRMATION);
        } catch (Error $e) {
            $json = json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        $event = new OrderEvent($order, [
            'channel' => OrderLog::CHANNEL_ADMIN,
        ]);
        $this->dispatcher->dispatch($event, OrderEvent::EMAIL_SENT_ORDER_CONFIRMATION);
        $this->addFlash('success', 'Email sikeresen elküldve!');

        return new Response('', 200);
    }

    /**
     * @Route("/orders/{id}/sendEmail-shippingConfirmation", name="order-sendEmail-shippingConfirmation")
     */
    public function sendShippingConfirmation(Request $request, ?Order $order, $id = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /orders/send-confirmation/{id}');
        }

        if (!$order) {
            $json = json_encode(['error' => 'Nincs ilyen rendelés!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        try {
            $this->emailSender->sendEmail($order, StoreEmailTemplate::SHIPPING_CONFIRMATION);
        } catch (Error $e) {
            $json = json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        $event = new OrderEvent($order, [
            'channel' => OrderLog::CHANNEL_ADMIN,
        ]);
        $this->dispatcher->dispatch($event, OrderEvent::EMAIL_SENT_SHIPPING_CONFIRMATION);
        $this->addFlash('success', 'Email sikeresen elküldve!');

        return new Response('', 200);
    }

    private function stringBetweenTwoStrings($str, $starting_word = '{{', $ending_word = '}}'){
        $arr = explode($starting_word, $str);
        if (isset($arr[1])){
            $arr = explode($ending_word, $arr[1]);
            return trim($arr[0], ' ');
        }
        return '';
    }

    /**
     * @Route("/orders/print/{id}", name="order-detail-print")
     */
    public function showOrderDetailPrint(Request $request, ?Order $order, $id = null, \App\Services\Localization $localization)
    {
        if (!$order) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen rendelés!' );
        }

        $shippingForm = $this->createForm(OrderShippingAddressType::class, $order);
        $billingForm = $this->createForm(OrderBillingAddressType::class, $order);

        $offset = GeneralUtils::DELIVERY_DATE_HOUR_OFFSET;
        $days = (new DateTime('+2 months'))->diff(new DateTime('now'))->days;
        for ($i = 0; $i <= $days; $i++) {
            /**
             * ($i*24 + offset) = 0x24+4 = 4 órával későbbi dátum lesz
             * Ez a '4' megegyezik azzal, amit a javascriptben adtunk meg, magyarán 4 órával
             * későbbi időpont az első lehetséges szállítási nap.
             */
            $dates[] = (new DateTime('+'. ($i*24 + $offset).' hours'));
        }

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
        $selectedIntervalFee = null === $order->getShippingFee() ? null : $order->getShippingFee();

        $hiddenDates = new HiddenDeliveryDate($selectedDate, $selectedInterval, $selectedIntervalFee);
        $hiddenDateForm = $this->createForm(CartHiddenDeliveryDateFormType::class, $hiddenDates);

        $statusForm = $this->createForm(OrderStatusType::class, $order);
        $paymentStatusForm = $this->createForm(PaymentStatusType::class, $order);

        $log = new OrderLog();

        $log->setChannel($this->getDoctrine()->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => OrderLog::CHANNEL_ADMIN]));
        $log->setOrder($order);
        $log->setComment(true);
//        $orderLog->setMessage('');
        $commentForm = $this->createForm(OrderCommentType::class, $log);

        return $this->render('admin/order/order-detail-print.html.twig', [
            'order' => $order,
            'shippingForm' => $shippingForm->createView(),
            'billingForm' => $billingForm->createView(),
            'generatedDates' => $generatedDates,
            'hiddenDateForm' => $hiddenDateForm->createView(),
            'selectedDate' => $selectedDate,
            'selectedInterval' => $selectedInterval,
            'statusForm' => $statusForm->createView(),
            'paymentStatusForm' => $paymentStatusForm->createView(),
            'commentForm' => $commentForm->createView(),
        ]);
    }
    
    /**
     * Edit the shipping address information in an Order. Used in AJAX.
     *
     * @Route("/orders/{id}/editShippingInfo", name="order-editShippingInfo", methods={"POST"})
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
            $order->setShippingFirstname($data->getShippingFirstname());
            $order->setShippingLastname($data->getShippingLastname());
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
     * Edit the shipping address information in an Order. Used in AJAX.
     *
     * @Route("/orders/{id}/editBillingInfo", name="order-editBillingInfo", methods={"POST"})
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
            $order->setBillingFirstname($data->getBillingFirstname());
            $order->setBillingLastname($data->getBillingLastname());
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
     * Edit the delivery date information in an Order. Used in AJAX.
     *
     * @Route("/orders/{id}/editDeliveryDate", name="order-editDeliveryDate", methods={"POST"})
     */
    public function editDeliveryDate(Request $request, ?Order $order, $id = null, HiddenDeliveryDate $date)
    {
        $date = $request->request->get('date');
        if (!$order) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        } else {
            $form = $this->createForm(CartHiddenDeliveryDateFormType::class, $date);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $order->setDeliveryDate(DateTime::createFromFormat('!Y-m-d', $data->getDeliveryDate()));
            $order->setDeliveryInterval($data->getDeliveryInterval());
            $order->setShippingFee($data->getDeliveryFee());

            $event = new OrderEvent($order, [
                'channel' => OrderLog::CHANNEL_ADMIN,
            ]);
            $this->dispatcher->dispatch($event, OrderEvent::DELIVERY_DATE_UPDATED);
    
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
            $html = $this->renderView('webshop/cart/hidden-delivery-date-form.html.twig', [
                'hiddenDateForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }

        $this->addFlash('success', 'Szállítási idő sikeresen módosítva!');
        $html = $this->render('admin/item.html.twig', [
            'item' => 'Szállítási idő sikeresen módosítva!',
        ]);
        return new Response($html, 200);//        return $this->redirectToRoute('order-detail', ['id' => $order->getId(),]);
    }

    /**
     * @Route("/orders/{id}/markAsFulfilled/", name="order-markAsFulfilled", methods={"POST"})
     */
    public function markAsFulfilled(Request $request, ?Order $order, $id = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /orders/{id}/markAsFulfilled');
        }

        if (!$order) {
            $json = json_encode(['error' => 'Nincs ilyen rendelés!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        if ($order->isFulfilled()) {
            $json = json_encode(['error' => 'Ez a rendelés már teljesítve lett!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        $isSendShippingConfirmation = true;
        if ($request->query->get('noShippingConfirmation')) {
            $isSendShippingConfirmation = false;
        }

        $status = $this->em->getRepository(OrderStatus::class)->findOneBy(['shortcode' => OrderStatus::STATUS_FULFILLED]);
        $order->setStatus($status);
        $this->em->persist($order);
        $this->em->flush();

        $event = new OrderEvent($order, [
            'channel' => OrderLog::CHANNEL_ADMIN,
            'status' => $status->getShortcode(),
        ]);
        $this->dispatcher->dispatch($event, OrderEvent::ORDER_UPDATED);

        if ($isSendShippingConfirmation) {
            try {
                $this->emailSender->sendEmail($order, StoreEmailTemplate::SHIPPING_CONFIRMATION);
            } catch (Error $e) {
                $json = json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
                return new JsonResponse($json,400, [], true);
            }
        }

        $event = new OrderEvent($order, [
            'channel' => OrderLog::CHANNEL_ADMIN,
        ]);
        $this->dispatcher->dispatch($event, OrderEvent::EMAIL_SENT_SHIPPING_CONFIRMATION);


        $this->addFlash('success', 'Rendelés sikeresen teljesítve!');
//        $html = $this->render('admin/item.html.twig', ['item' => 'Rendelés sikeresen teljesítve!']);
        return new Response('', 200);
    }

    /**
     * @Route("/orders/{id}/markAsPaid", name="order-markAsPaid", methods={"POST"})
     */
    public function markAsPaid(Request $request, ?Order $order, $id = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /orders/{id}/markAsPaid');
        }

        if (!$order) {
            $json = json_encode(['error' => 'Nincs ilyen rendelés!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        if ($order->isPaid()) {
            $json = json_encode(['error' => 'Ez a rendelés már ki van fizetve!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        $transaction = $order->getTransaction();
        if (!$transaction) {
            $json = json_encode(['error' => 'A rendeléshez nem tartozik tranzakció!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json, 400, [], true);
        }

        if ($transaction->getGateway() === PaymentBuilder::MANUAL_COD || $transaction->getGateway() === PaymentBuilder::MANUAL_BANK) {
            $transaction->setStatus(PaymentTransaction::STATUS_SUCCESS);
            $transaction->setProcessedAt(new DateTime('now'));
            $paymentStatus = $this->paymentBuilder->computePaymentStatus($transaction);
//                $order->setPaymentStatus($paymentStatus);
//                $this->em->persist($order);
            $this->em->persist($transaction);
            $this->em->flush();

            $event = new OrderEvent($order, [
                'channel' => OrderLog::CHANNEL_ADMIN,
                'status' => $paymentStatus->getShortcode(),
            ]);
            $this->dispatcher->dispatch($event, OrderEvent::PAYMENT_UPDATED);

            $this->addFlash('success', 'Fizetés állapota sikeresen módosítva!');
            return new Response('', 200);
        }

        $json = json_encode(['error' => 'Ismeretlen hiba történt!'], JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json,400, [], true);
    }

    /**
     * @Route("/orders/{id}/cancelOrder", name="order-cancelOrder", methods={"POST"})
     */
    public function cancelOrder(Request $request, ?Order $order, $id = null)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('HIBA: /orders/{id}/cancelOrder');
        }

        if (!$order) {
            $json = json_encode(['error' => 'Nincs ilyen rendelés!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        if ($order->isClosed()) {
            $json = json_encode(['error' => 'Ez a rendelés már le van zárva!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }
        if ($order->isCanceled()) {
            $json = json_encode(['error' => 'Ez a rendelés már törölve volt!'], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($json,400, [], true);
        }

        $order->setCanceledAt(new DateTime('now'));
        $this->em->persist($order);
        $this->em->flush();

        $event = new OrderEvent($order, [
            'channel' => OrderLog::CHANNEL_ADMIN,
            'status' => OrderStatus::ORDER_CANCELED,
        ]);
        $this->dispatcher->dispatch($event, OrderEvent::ORDER_UPDATED);

        $this->addFlash('success', 'Rendelés sikeresen törölve!');
        return new Response('', 200);
    }



    /**
     * NINCS HASZNALATBAN !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Edit the delivery date information in an Order. Used in AJAX.
     *
     * @Route("/orders/{id}/editStatus", name="order-editStatus", methods={"POST"})
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

            $event = new OrderEvent($order, [
                'channel' => OrderLog::CHANNEL_ADMIN,
                'orderStatus' => $order->getStatus()->getShortcode(),
            ]);
            $this->dispatcher->dispatch($event, OrderEvent::ORDER_UPDATED);
        
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

    /**
     * NINCS HASZNALATBAN !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Edit the Payment Status in an Order. Used in AJAX.
     *
     * @Route("/orders/{id}/editPaymentStatus", name="order-editPaymentStatus", methods={"POST"})
     */
    public function editPaymentStatus(Request $request, ?Order $order, $id = null)
    {
        if (!$order) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        } else {
            $form = $this->createForm(PaymentStatusType::class, $order);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Order $data */
            $data = $form->getData();

//            $status = $data->getPaymentStatus()->getShortcode();
            $transaction = $order->getTransaction();

            if ($transaction->getGateway() === PaymentBuilder::MANUAL_COD || $transaction->getGateway() === PaymentBuilder::MANUAL_BANK) {
                $transaction->setStatus(PaymentTransaction::STATUS_SUCCESS);
                $transaction->setProcessedAt(new DateTime('now'));
                $paymentStatus = $this->paymentBuilder->computePaymentStatus($transaction);
//                $order->setPaymentStatus($paymentStatus);

//                $this->em->persist($order);
                $this->em->persist($transaction);
                $this->em->flush();

                $event = new OrderEvent($order, [
                    'channel' => OrderLog::CHANNEL_ADMIN,
                    'status' => $paymentStatus->getShortcode(),
                ]);
                $this->dispatcher->dispatch($event, OrderEvent::PAYMENT_UPDATED);
            }

//            $event = new OrderEvent($order, [
//                'channel' => OrderLog::CHANNEL_ADMIN,
//                'paymentStatus' => $data->getPaymentStatus()->getShortcode(),
//            ]);
//            $this->dispatcher->dispatch($event, OrderEvent::PAYMENT_UPDATED);
//
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($order);
//            $entityManager->flush();

            /**
             * If AJAX request, and because at this point the form data is processed, it returns Success (code 200)
             */
            if ($request->isXmlHttpRequest()) {
                $this->addFlash('success', 'Fizetés állapota sikeresen módosítva!');
                $html = $this->render('admin/item.html.twig', [
                    'item' => 'Fizetés állapota sikeresen módosítva!',
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
                'paymentStatusForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }
    }

    /**
     * Add a comment to an Order. In fact this is just a simple OrderLog added to the Order.
     * Used in AJAX.
     *
     * @Route("/orders/{orderId}/editComment/", name="order-editComment", methods={"POST"})
     */
    public function editCommentForm(Request $request, ?OrderLog $log, $orderId = null) //, $id = null
    {
        if (!$orderId) {
            throw $this->createNotFoundException("STUPID: Nincs ilyen rendelés!");
        }
        $order = $this->getDoctrine()->getRepository(Order::class)->find($orderId);

        if (!$log) {
            $log = new OrderLog();
            $log->setOrder($order);
            $log->setComment(true);
            $log->setChannel($this->getDoctrine()->getRepository(OrderLogChannel::class)->findOneBy(['shortcode' => OrderLog::CHANNEL_ADMIN]));
            $form = $this->createForm(OrderCommentType::class, $log);
        } else {
            $form = $this->createForm(OrderCommentType::class, $log);
            $order = $log->getOrder();
        }
        $form->handleRequest($request);
//        dd($form->isSubmitted() && $request->isXmlHttpRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var OrderLog $data */
            $log = $form->getData();
            $order = $log->getOrder();

//            $log->setMessage($data->getMessage());
//            $log->setOrder($order);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($log);
            $entityManager->flush();

            /**
             * If AJAX request, returns the list of Recipients
             */
            if ($request->isXmlHttpRequest()) {
                $this->addFlash('success', 'Komment sikeresen hozzáadva!');
                $html = $this->render('admin/item.html.twig', [
                    'item' => 'Komment sikeresen hozzáadva!',
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
            $html = $this->renderView('admin/order/_form-order-comment.html.twig', [
                'order' => $order,
                'commentForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }

        /**
         * Renders form initially with data
         */
        return $this->render('admin/order/_form-order-comment.html.twig', [
            'order' => $order,
            'commentForm' => $form->createView(),
        ]);
    }
}