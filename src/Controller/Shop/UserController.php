<?php

namespace App\Controller\Shop;

use App\Entity\Model\CustomerBasic;
use App\Entity\Order;
use App\Form\CustomerBasic\CustomerBasicsFormType;
use App\Services\StoreSettings;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
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
//        $queryBuilder = $em->getRepository(Order::class)->findAllQuery();
        $orders = $this->getUser()->getOrdersPlaced()->getValues();

//        $adapter = new DoctrineORMAdapter($queryBuilder);
        $adapter = new ArrayAdapter($orders);
        $pagerfanta = new Pagerfanta($adapter);
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
     * @Route("/my-account/my-details", name="site-user-myDetails")
     */
    public function showMyDetails()
    {
        $customer = new CustomerBasic(
            $this->getUser()->getEmail(),
            $this->getUser()->getFirstname(),
            $this->getUser()->getLastname(),
            $this->getUser()->getPhone()
        );
        $form = $this->createForm(CustomerBasicsFormType::class,$customer);
        return $this->render('webshop/site/user-myDetails.html.twig', [
            'customer' => $this->getUser(),
            'customerForm' => $form->createView(),
        ]);
    }

}