<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserRegistration\UserFilterType;
use App\Services\StoreSettings;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class UserAccountController extends AbstractController
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;
    private $roles;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @Route("/users", name="user-list",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function listUsers(Request $request, $page = 1, StoreSettings $settings, TranslatorInterface $translator)
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
            $filterTags['dateRange'] = $translator->trans('user.filter.filter-by-date-result') . ' ' . $dateRange;
            $data['dateRange'] = $dateRange;
            $urlParams['dateRange'] = $dateRange;
        }
        if ($searchTerm) {
            $filterTags['searchTerm'] = $translator->trans('user.filter.search-result').' '.$searchTerm;
            $data['searchTerm'] = $searchTerm;
            $urlParams['searchTerm'] = $searchTerm;
        }
        if ($status !== null) {
            if ($status == true) {
                $filterTags['status'] = $translator->trans('user.filter.active-users');
            }
            if ($status == false) {
                $filterTags['status'] = 'disabled';
            }
//            $filterTags['status'] = $status;
            $urlParams['status'] = $status;
            $data['status'] = $status;
        }
        $filterForm = $this->createForm(UserFilterType::class, $data);

        $filterUrls = [];
        foreach ($filterTags as $key => $value) {
            // remove the current filter from the urlParams
            $shortlist = array_diff_key($urlParams,[$key => '']);

            // generate the URL with the remaining filters
            $filterUrls[$key] = $this->generateUrl('user-list',[
                'dateRange' => isset($shortlist['dateRange']) ? $shortlist['dateRange'] : null,
                'searchTerm' => isset($shortlist['searchTerm']) ? $shortlist['searchTerm'] : null,
                'status' => isset($shortlist['status']) ? $shortlist['status'] : null,
            ]);
        }

        $filterQuickLinks['all'] = [
            'name' => $translator->trans('user.filter.all-users'),
            'url' => $this->generateUrl('user-list'),
            'active' => false,
        ];
        $filterQuickLinks['enabled'] = [
            'name' => $translator->trans('user.filter.active-users'),
            'url' => $this->generateUrl('user-list',['status' => 1]),
            'active' => false,
        ];
        $filterQuickLinks['disabled'] = [
            'name' => 'disabled', //$translator->trans('user.filter.active-users'),
            'url' => $this->generateUrl('user-list',['status' => 0]),
            'active' => false,
        ];
//        $filterQuickLinks['unfulfilled'] = [
//            'name' => 'Feldolgozás alatt',
//            'url' => $this->generateUrl('order-list-table',['orderStatus' => OrderStatus::ORDER_CREATED]),
//            'active' => false,
//        ];

        // Generate the quicklinks which are placed above the filter
        $hasCustomFilter = false;
        if (!$dateRange && $status === null && !$searchTerm) {
            $filterQuickLinks['all']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $status !== null && $status == 1) {
            $filterQuickLinks['enabled']['active'] = true;
            $hasCustomFilter = true;
        }
        if (!$dateRange && $status !== null && $status == 0) {
            $filterQuickLinks['disabled']['active'] = true;
            $hasCustomFilter = true;
        }

//        if (!$dateRange && $orderStatus && $orderStatus === OrderStatus::ORDER_CREATED) {
//            $filterQuickLinks['unfulfilled']['active'] = true;
//            $hasCustomFilter = true;
//        }
        if (!$hasCustomFilter) {
            $filterQuickLinks['custom'] = [
                'name' => $translator->trans('user.filter.custom-filter'),
                'url' => $this->generateUrl('user-list',$request->query->all()),
                'active' => true,
            ];
        }

        $queryBuilder = $em->getRepository(User::class)->findAllQuery([
            'dateRange' => $dateRange,
            'searchTerm' => $searchTerm,
            'status' => $status,
        ]);



//        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
//        $pagerfanta = new Pagerfanta(new ArrayAdapter($users));

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $users = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $users[] = $result;
        }

        if (!$users) {
            $this->addFlash('danger', $translator->trans('user.users-not-found'));
        }

        return $this->render('admin/user/user-list.html.twig', [
            'users' => $users,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
            'filterQuickLinks' => $filterQuickLinks,
            'filterForm' => $filterForm->createView(),
            'filterTags' => $filterTags,
            'filterUrls' => $filterUrls,
        ]);
    }

    /**
     * @Route("/users/filter", name="user-list-filter")
     */
    public function handleFilterForm(Request $request)
    {
        $form = $this->createForm(UserFilterType::class);
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
            if ($filters['status'] !== null) {
                $status = $filters['status'];
            }

            return $this->redirectToRoute('user-list',[
                'dateRange' => $dateRange,
                'searchTerm' => $searchTerm,
                'status' => $status,
            ]);
        }
        return $this->redirectToRoute('user-list');
    }

    /**
     * @Route("/users/{id}", name="user-show")
     */
    public function showUserProfiler(User $user)
    {
        if (!$user) {
            throw $this->createNotFoundException('Nincs ilyen felhasználó!');
        }

        $orders = null;
        $customer = $user->getCustomer();

        return $this->render('admin/user/user-detail.html.twig', [
            'user' => $user,
            'customer' => $customer,
            'inheritedRoles' => $this->roles,
        ]);
    }
}