<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Model\OrdersSummary;
use App\Entity\Payment;
use App\Entity\PaymentStatus;
use App\Entity\Shipping;
use App\Form\ShippingFormType;
use App\Services\HelperFunction;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    /**
     * @Route("/", name="admin")
     */
    public function showAdmin()
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->redirectToRoute('admin-login');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function showDashboard(HelperFunction $function)
    {
        $rep = $this->getDoctrine()->getRepository(Order::class);
//        $orders = $this->getDoctrine()->getRepository(Order::class)->findAllLast('24 hours');
        $orderCount = $rep->countAllLast('24 hours');
        $unpaidCount = $rep->countAllLast('24 hours', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast('24 hours', ['orderStatus' => OrderStatus::STATUS_CREATED]);

        $lastDay = new OrdersSummary();
        $lastDay->setOrderCount($orderCount['count']);
//        $lastDay->setTotalRevenue($revenue);
        $lastDay->setUnpaidCount($unpaidCount['count']);
        $lastDay->setUnfulfilledCount($unfulfilledCount['count']);
        $lastDay->setDateRange($function->createDateRange('24 hours'));

        $orderCount = $rep->countAllLast('7 days');
        $unpaidCount = $rep->countAllLast('7 days', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast('7 days', ['orderStatus' => OrderStatus::STATUS_CREATED]);

        $lastWeek = new OrdersSummary();
        $lastWeek->setOrderCount($orderCount['count']);
//        $lastWeek->setTotalRevenue($revenue);
        $lastWeek->setUnpaidCount($unpaidCount['count']);
        $lastWeek->setUnfulfilledCount($unfulfilledCount['count']);
        $lastWeek->setDateRange($function->createDateRange('7 days'));

        $orderCount = $rep->countAllLast('30 days');
//        dd($orderCount);
        $unpaidCount = $rep->countAllLast('30 days', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast('30 days', ['orderStatus' => OrderStatus::STATUS_CREATED]);

        $lastMonth = new OrdersSummary();
        $lastMonth->setOrderCount($orderCount['count']);
//        $lastMonth->setTotalRevenue($revenue);
        $lastMonth->setUnpaidCount($unpaidCount['count']);
        $lastMonth->setUnfulfilledCount($unfulfilledCount['count']);
        $lastMonth->setDateRange($function->createDateRange('30 days'));

        $orderCount = $rep->countAllLast('lifetime');
        $unpaidCount = $rep->countAllLast('lifetime', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast(null, ['orderStatus' => OrderStatus::STATUS_CREATED]);

        $lifetime = new OrdersSummary();
        $lifetime->setOrderCount($orderCount['count']);
        $lifetime->setUnpaidCount($unpaidCount['count']);
        $lifetime->setUnfulfilledCount($unfulfilledCount['count']);

        return $this->render('admin/dashboard.html.twig', [
            'lastDay' => $lastDay,
            'lastWeek' => $lastWeek,
            'lastMonth' => $lastMonth,
            'lifetime' => $lifetime,

        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_SHIPPING")
     * @Route("/shipping", name="shipping-list")
     */
    public function listShippingMethods()
    {
        $shippings = $this->getDoctrine()
            ->getRepository(Shipping::class)
            ->findAll();
        $payments = $this->getDoctrine()
            ->getRepository(Payment::class)
            ->findAll();

        $noResult = '';
        if (!$shippings || !$payments) {
            //throw $this->createNotFoundException('Nem talált egy terméket sem!');
            $noResult = 'Nem talált ilyen adatot!';
        }

        return $this->render('admin/delivery-methods-list.html.twig', [
            'title' => 'Szállítási és fizetési módok',
            'shippings' => $shippings,
            'payments' => $payments,
            'noResult' => $noResult,
        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_SHIPPING")
     * @Route("/shipping/edit/{id}", name="shipping-edit")
     */
    public function editShipping(Request $request, ?Shipping $shipping, $id = null)
    {
        if (!$shipping) {
            /**
             * new Shipping
             */
            $form = $this->createForm(ShippingFormType::class);
            $title = 'Új szállítási mód';
        } else {
            /**
             * edit existing Shipping
             */
            $form = $this->createForm(ShippingFormType::class, $shipping);
            $title = 'Szállítási mód módosítása';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipping = $form->getData();
            $shipping->setUpdatedAt(new DateTime('NOW'));
            $shipping->setCreatedAt(new DateTime('NOW'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($shipping);
            $entityManager->flush();

            $this->addFlash('success', 'Szállítási mód sikeresen elmentve!');

            return $this->redirectToRoute('shipping-list');

        }

        return $this->render('admin/shipping-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }


}