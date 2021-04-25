<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Model\OrdersSummary;
use App\Entity\PaymentMethod;
use App\Entity\PaymentStatus;
use App\Entity\ShippingMethod;
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
        $unfulfilledCount = $rep->countAllLast('24 hours', ['orderStatus' => OrderStatus::ORDER_CREATED]);

        $lastDay = new OrdersSummary();
        $lastDay->setOrderCount($orderCount['count']);
//        $lastDay->setTotalRevenue($revenue);
        $lastDay->setUnpaidCount($unpaidCount['count']);
        $lastDay->setUnfulfilledCount($unfulfilledCount['count']);
        $lastDay->setDateRange($function->createDateRange('24 hours'));

        $orderCount = $rep->countAllLast('7 days');
        $unpaidCount = $rep->countAllLast('7 days', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast('7 days', ['orderStatus' => OrderStatus::ORDER_CREATED]);

        $lastWeek = new OrdersSummary();
        $lastWeek->setOrderCount($orderCount['count']);
//        $lastWeek->setTotalRevenue($revenue);
        $lastWeek->setUnpaidCount($unpaidCount['count']);
        $lastWeek->setUnfulfilledCount($unfulfilledCount['count']);
        $lastWeek->setDateRange($function->createDateRange('7 days'));

        $orderCount = $rep->countAllLast('30 days');
//        dd($orderCount);
        $unpaidCount = $rep->countAllLast('30 days', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast('30 days', ['orderStatus' => OrderStatus::ORDER_CREATED]);

        $lastMonth = new OrdersSummary();
        $lastMonth->setOrderCount($orderCount['count']);
//        $lastMonth->setTotalRevenue($revenue);
        $lastMonth->setUnpaidCount($unpaidCount['count']);
        $lastMonth->setUnfulfilledCount($unfulfilledCount['count']);
        $lastMonth->setDateRange($function->createDateRange('30 days'));

        $orderCount = $rep->countAllLast('lifetime');
        $unpaidCount = $rep->countAllLast('lifetime', ['paymentStatus' => PaymentStatus::STATUS_PENDING]);
        $unfulfilledCount = $rep->countAllLast(null, ['orderStatus' => OrderStatus::ORDER_CREATED]);

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
}