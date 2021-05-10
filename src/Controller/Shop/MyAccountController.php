<?php

namespace App\Controller\Shop;

use App\Entity\Customer;
use App\Entity\Order;
use App\Services\OrderBuilder;
use App\Entity\User;
use App\Form\Customer\CustomerType;
use App\Services\StoreSettings;
use DateTime;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyAccountController extends AbstractController
{
    /**
     * @Route("/myaccount", name="site-user-myAccount")
     */
    public function showMyAccount()
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $customer = $this->getUser()->getCustomer();
        $orders = [];
        if ($customer) {
            $orders = $customer->getOrdersPlaced();

            if ($orders) {
                $orders = $orders->getValues();
            }
        }

        return $this->render('webshop/user/user-myAccount.html.twig', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/myaccount/orders/", name="site-user-myOrders",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function showMyOrders(Request $request, $page = 1, StoreSettings $settings)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $page = $request->query->get('page') ? $request->query->get('page') : $page;
        $customer = $this->getUser()->getCustomer();
        $orders = [];
        if ($customer) {
            $orders = $customer->getOrdersPlaced();

            if ($orders) {
                $orders = $orders->getValues();
            }
        }

        $pagerfanta = new Pagerfanta(new ArrayAdapter($orders));
//        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        $pagerfanta->setMaxPerPage(5);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $orders = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $orders[] = $result;
        }

        return $this->render('webshop/user/user-myOrders.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/myaccount/orders/{id}", name="site-user-myOrder")
     */
    public function showMyOrder(Request $request, ?Order $order, $id = null)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        if (!$order) {
            $this->addFlash('danger', 'Nem talált ilyen rendelést!');
            return $this->redirectToRoute('site-user-myAccount');
        }

        /** @var Customer $customer*/
        $customer = $this->getUser()->getCustomer();
        if (!$customer) {
            $this->addFlash('danger', 'Nem talált ilyen rendelést!');
            return $this->redirectToRoute('site-user-myAccount');
        }

        if ($customer->getPlacedOrdersCount() === 0) {
            $this->addFlash('danger', 'Nem talált ilyen rendelést!');
            return $this->redirectToRoute('site-user-myAccount');
        }

        return $this->render('webshop/user/user-myOrder.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/myaccount/details/{id}", name="site-user-myDetails")
     */
    public function showMyDetails(Request $request, ?Customer $customer, $id=null, OrderBuilder $orderBuilder)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        if (!$customer) {
            $customer = $this->getUser()->getCustomer();
        }

        $form = $this->createForm(CustomerType::class, $customer, ['urlName' => 'site-user-myDetails']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Customer $data */
            $data = $form->getData();

            /** @var User $user */
            $user = $this->getUser();
            if ($user) {
                $user->setFirstname($data->getFirstname());
                $user->setLastname($data->getLastname());
                $user->setAcceptsMarketing($data->isAcceptsMarketing());
                $data->setAcceptsMarketingUpdatedAt(new DateTime('now'));
                if ($data->isAcceptsMarketing()) {
                    $data->setMarketingOptinLevel(Customer::OPTIN_LEVEL_SINGLE_OPTIN);
                } else {
                    $data->setMarketingOptinLevel(Customer::OPTIN_LEVEL_OPT_OUT);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->persist($data);
            $em->flush();
        }

        return $this->render('webshop/user/user-myDetails.html.twig', [
            'customerForm' => $form->createView(),
        ]);
    }
}