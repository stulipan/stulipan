<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Form\Customer\CustomerFilterType;
use App\Services\StoreSettings;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function listCustomers(Request $request, $page = 1, StoreSettings $settings, TranslatorInterface $translator)
    {
        $dateRange = $request->query->get('dateRange');
        $searchTerm = $request->query->get('searchTerm');
        $acceptsMarketing = $request->query->get('acceptsMarketing');
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
            $filterTags['searchTerm'] = $translator->trans('customer.filter.search-result').' '.$searchTerm;
            $data['searchTerm'] = $searchTerm;
            $urlParams['searchTerm'] = $searchTerm;
        }
        if ($acceptsMarketing !== null) {
            if ($acceptsMarketing == true) {
                $filterTags['acceptsMarketing'] = $translator->trans('customer.filter.accepts-marketing') . ': ' . $translator->trans('customer.filter.filter-by-accepts-marketing-subscribed');
            }
            if ($acceptsMarketing == false) {
                $filterTags['acceptsMarketing'] = $translator->trans('customer.filter.accepts-marketing') . ': ' . $translator->trans('customer.filter.filter-by-accepts-marketing-no');
            }
            $urlParams['acceptsMarketing'] = $acceptsMarketing;
            $data['acceptsMarketing'] = $acceptsMarketing;
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
                'acceptsMarketing' => isset($shortlist['acceptsMarketing']) ? $shortlist['acceptsMarketing'] : null,
            ]);
        }

        $filterQuickLinks['all'] = [
            'name' => $translator->trans('customer.filter.all-customers'),
            'url' => $this->generateUrl('customer-list'),
            'active' => false,
        ];
        $filterQuickLinks['enabled'] = [
            'name' => $translator->trans('customer.filter.accepts-marketing'),
            'url' => $this->generateUrl('customer-list',['acceptsMarketing' => 1]),
            'active' => false,
        ];
//        $filterQuickLinks['unfulfilled'] = [
//            'name' => 'Feldolgozás alatt',
//            'url' => $this->generateUrl('order-list-table',['orderStatus' => OrderStatus::ORDER_CREATED]),
//            'active' => false,
//        ];

        // Generate the quicklinks which are placed above the filter
        $hasCustomFilter = false;
        if (!$dateRange && $acceptsMarketing === null && !$searchTerm) {
            $filterQuickLinks['all']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $acceptsMarketing !== null && $acceptsMarketing == 1) {
            $filterQuickLinks['enabled']['active'] = true;
            $hasCustomFilter = true;
        }
//        if (!$dateRange && $orderStatus && $orderStatus === OrderStatus::ORDER_CREATED) {
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

        $queryBuilder = $em->getRepository(Customer::class)->findAllQuery([
            'dateRange' => $dateRange,
            'searchTerm' => $searchTerm,
            'acceptsMarketing' => $acceptsMarketing,
        ]);

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
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
            $acceptsMarketing = null;
            $acceptsMarketing = null;

            if ($filters['dateRange']) {
                $dateRange = $filters['dateRange'];
            }
            if ($filters['searchTerm']) {
                $searchTerm = $filters['searchTerm'];
            }
            if ($filters['acceptsMarketing'] !== null) {
                $acceptsMarketing = $filters['acceptsMarketing'];
            }
            return $this->redirectToRoute('customer-list',[
                'dateRange' => $dateRange,
                'searchTerm' => $searchTerm,
                'acceptsMarketing' => $acceptsMarketing,
            ]);
        }
        return $this->redirectToRoute('customer-list');
    }

    /**
     * @Route("/customers/{id}", name="customer-show")
     */
    public function showCustomerProfile(Customer $customer)
    {
        if (!$customer) {
            throw $this->createNotFoundException('Nincs ilyen vásárló!');
        }

        $totalRevenue = 0;
        $orderCount = 0;

        $orders = $customer->getOrdersPlaced();

        if ($orders) {
            $orderCount = $orders->count();
            foreach ($orders as $o => $order) {
                $totalRevenue += $order->getSummary()->getTotalAmountToPay();
            }
        }

        return $this->render('admin/customer-detail.html.twig', [
            'customer' => $customer,
            'orders' => $orders,
            'orderCount' => $orderCount,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}