<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\CustomerFilterType;
use App\Services\Settings;
use Cassandra\Custom;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_MANAGE_CUSTOMERS")
 * @Route("/admin")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/customers", name="customer-list",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function listOrders(Request $request, $page = 1, Settings $settings, TranslatorInterface $translator)
    {
        $dateRange = $request->query->get('dateRange');
        $searchTerm = $request->query->get('searchTerm');
        $status = $request->query->get('status');
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
            $filterTags['searchTerm'] = $translator->trans('customer.search-result').' '.$searchTerm;
            $data['searchTerm'] = $searchTerm;
            $urlParams['searchTerm'] = $searchTerm;
        }
        if ($status) {
//            $filterTags['status'] = $em->getRepository(status::class)->findOneBy(['shortcode' => $status])->getName();
            $filterTags['status'] = $status;
            $urlParams['status'] = $status;
//            $data['status'] = $em->getRepository(status::class)->findOneBy(['shortcode' => $status]);
            $data['status'] = $status;
        }
        $filterForm = $this->createForm(CustomerFilterType::class, $data);

        $filterUrls = [];
        foreach ($filterTags as $key => $value) {
            // remove the current filter from the urlParams
            $shortlist = array_diff_key($urlParams,[$key => '']);

            // generate the URL with the remaining filters
            $filterUrls[$key] = $this->generateUrl('customer-list',[
                'dateRange' => isset($shortlist['dateRange']) ? $shortlist['dateRange'] : null,
                'searchTerm' => isset($shortlist['searchTerm']) ? $shortlist['searchTerm'] : null,
                'status' => isset($shortlist['status']) ? $shortlist['status'] : null,
            ]);
        }

        $filterQuickLinks['all'] = [
            'name' => $translator->trans('customer.filter.all-customers'),
            'url' => $this->generateUrl('customer-list'),
            'active' => false,
        ];
        $filterQuickLinks['enabled'] = [
            'name' => $translator->trans('customer.filter.active-customers'),
            'url' => $this->generateUrl('customer-list',['status' => 1]),
            'active' => false,
        ];
//        $filterQuickLinks['unfulfilled'] = [
//            'name' => 'Feldolgozás alatt',
//            'url' => $this->generateUrl('order-list-table',['orderStatus' => OrderStatus::STATUS_CREATED]),
//            'active' => false,
//        ];

        // Generate the quicklinks which are placed above the filter
        $hasCustomFilter = false;
        if (!$dateRange && !$status && !$searchTerm) {
            $filterQuickLinks['all']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $status && $status == 1) {
            $filterQuickLinks['enabled']['active'] = true;
            $hasCustomFilter = true;
        }
//        if (!$dateRange && $orderStatus && $orderStatus === OrderStatus::STATUS_CREATED) {
//            $filterQuickLinks['unfulfilled']['active'] = true;
//            $hasCustomFilter = true;
//        }
        if (!$hasCustomFilter) {
            $filterQuickLinks['custom'] = [
                'name' => $translator->trans('customer.filter.custom-filter'),
                'url' => $this->generateUrl('customer-list',$request->query->all()),
                'active' => true,
            ];
        }

        $queryBuilder = $em->getRepository(User::class)->findAllQuery([
            'dateRange' => $dateRange,
            'searchTerm' => $searchTerm,
            'status' => $status,
        ]);

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        //$pagerfanta->setCurrentPage($page);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $customers = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $customers[] = $result;
        }

        if (!$customers) {
//            $this->addFlash('danger', 'Nem talált rendeléseket! Próbáld módosítani a szűrőket.');
        }

        return $this->render('admin/customer-list.html.twig', [
            'customers' => $customers,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
//            'count' => count($customers),
//            'orderCount' => empty($customers) ? 'Nincsenek rendelések' : count($customers),
            'filterQuickLinks' => $filterQuickLinks,
            'filterForm' => $filterForm->createView(),
            'filterTags' => $filterTags,
            'filterUrls' => $filterUrls,
        ]);
    }

    /**
     * @Route("/customers/filter", name="customer-list-filter")
     */
    public function handleFilterForm(Request $request)
    {
        $form = $this->createForm(CustomerFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $dateRange = null;
            $searchTerm = null;
            $status = null;
            $status = null;

            if ($filters['dateRange']) {
                $dateRange = $filters['dateRange'];
            }
            if ($filters['searchTerm']) {
                $searchTerm = $filters['searchTerm'];
            }
            if ($filters['status']) {
                $status = $filters['status'];
            }
//            dd($status);
            return $this->redirectToRoute('customer-list',[
                'dateRange' => $dateRange,
                'searchTerm' => $searchTerm,
                'status' => $status,
            ]);
        }
        return $this->redirectToRoute('customer-list');
    }
    /**
     * @Route("/customers/{id}", name="customer-show")
     */
    public function showCustomerProfile(User $user)
    {
        if (!$user) {
            throw $this->createNotFoundException('Nincs ilyen vásárló!');
            //return $this->redirectToRoute('404');
        }
//        $shippings = $this->getDoctrine()
//            ->getRepository(Shipping::class)
//            ->findAll();
//        $payments = $this->getDoctrine()
//            ->getRepository(Payment::class)
//            ->findAll();

//        $noOrders = '';
//        if (!$shippings || !$payments) {
//            //throw $this->createNotFoundException('Nem talált egy terméket sem!');
//            $noResult = 'Nem talált ilyen adatot!';
//        }

        $totalRevenue = 0;
        foreach ($user->getOrdersPlaced() as $o => $order) {
            $totalRevenue += $order->getSummary()->getTotalAmountToPay();
        }

        return $this->render('admin/customer-profile-show.html.twig', [
            'user' => $user,
//            'orders' => $user->getOrdersPlaced(),
//            'shippings' => $shippings,
//            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
        ]);
    }


}