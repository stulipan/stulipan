<?php

namespace App\Controller\Shop;

use App\Entity\Customer;
use App\Entity\Order;
use App\Services\OrderBuilder;
use App\Entity\User;
use App\Form\Customer\CustomerType;
use App\Services\StoreSettings;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyAccountController extends AbstractController
{

    /**
     * @Route("/my-account", name="site-user-myAccount")
     */
    public function showMyAccount()
    {
        return $this->render('webshop/site/user-myAccount.html.twig', [
            'customer' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/my-account/orders/", name="site-user-myOrders",
     *     requirements={"page"="\d+"},
     *     )
     */
    public function showMyOrders(Request $request, $page = 1, StoreSettings $settings)
    {
        $page = $request->query->get('page') ? $request->query->get('page') : $page;
        $orders = $this->getUser()->getCustomer()->getOrdersPlaced();

        $pagerfanta = new Pagerfanta(new ArrayAdapter($orders->getValues()));
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

        return $this->render('webshop/site/user-myOrders.html.twig', [
            'orders' => $orders,
            'paginator' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/my-account/orders/{id}", name="site-user-myOrder")
     */
    public function showMyOrder(Request $request, ?Order $order, $id = null)
    {
        if (!$order) {
            throw $this->createNotFoundException('STUPID: Nincs ilyen rendelés!' );
        }
        if (!$this->getUser()->hasOrder($order)) {
            $this->addFlash('danger', 'Nem talált ilyen rendelést!');
            return $this->redirect('site-user-myAccount');
        }
        return $this->render('webshop/site/user-myOrder.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/my-account/my-details/{id}", name="site-user-myDetails")
     */
    public function showMyDetails(Request $request, ?Customer $customer, $id=null, OrderBuilder $orderBuilder)
    {
        if (!$customer) {
            $customer = $orderBuilder->getCustomer();
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

                // TODO implement acceptsMarketingUpdatedAt
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->persist($data);
            $em->flush();
        }

        return $this->render('webshop/site/user-myDetails.html.twig', [
//            'customer' => $this->getUser(),
            'customerForm' => $form->createView(),
        ]);
    }

}