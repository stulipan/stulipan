<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Model\OrdersSummary;
use App\Entity\PaymentStatus;
use App\Services\HelperFunction;
use App\Services\Localization;
use Stulipan\Traducible\Builder\LocaleStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $session;
    private $localeStorage;
    private $localization;

    public function __construct(SessionInterface $session, LocaleStorage $localeStorage, Localization $localization)
    {
        $this->session = $session;
        $this->localeStorage = $localeStorage;
        $this->localization = $localization;
    }

    /**
     * @Route("/", name="admin")
     */
    public function showAdmin()
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
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
        $orderCount = $rep->countLast(['period' => '24 hours']);
//        $unpaidCount = $rep->countLast(['period' => '24 hours', 'paymentStatus' => PaymentStatus::STATUS_PENDING]);
//        $unfulfilledCount = $rep->countLast(['period' => '24 hours', 'orderStatus' => OrderStatus::ORDER_CREATED]);
        $totalRevenue = $rep->sumLast(['period' => '24 hours']);

        $lastDay = new OrdersSummary();
        $lastDay->setOrderCount($orderCount);
//        $lastDay->setUnpaidCount($unpaidCount);
//        $lastDay->setUnfulfilledCount($unfulfilledCount);
        $lastDay->setTotalRevenue($totalRevenue);
        $lastDay->setDateRange($function->createDateRange('24 hours'));


        $orderCount = $rep->countLast(['period' => '7 days']);
//        $unpaidCount = $rep->countLast(['period' => '7 days', 'paymentStatus' => PaymentStatus::STATUS_PENDING]);
//        $unfulfilledCount = $rep->countLast(['period' => '7 days', 'orderStatus' => OrderStatus::ORDER_CREATED]);
        $totalRevenue = $rep->sumLast(['period' => '7 days']);

        $lastWeek = new OrdersSummary();
        $lastWeek->setOrderCount($orderCount);
//        $lastWeek->setUnpaidCount($unpaidCount);
//        $lastWeek->setUnfulfilledCount($unfulfilledCount);
        $lastWeek->setTotalRevenue($totalRevenue);
        $lastWeek->setDateRange($function->createDateRange('7 days'));

        $orderCount = $rep->countLast(['period' => '30 days']);
//        $unpaidCount = $rep->countLast(['period' => '30 days', 'paymentStatus' => PaymentStatus::STATUS_PENDING, 'isCanceled' => false]);
//        $unfulfilledCount = $rep->countLast(['period' => '30 days', 'orderStatus' => OrderStatus::ORDER_CREATED, 'isCanceled' => false]);
        $totalRevenue = $rep->sumLast(['period' => '30 days']);

        $lastMonth = new OrdersSummary();
        $lastMonth->setOrderCount($orderCount);
//        $lastMonth->setUnpaidCount($unpaidCount);
//        $lastMonth->setUnfulfilledCount($unfulfilledCount);
        $lastMonth->setTotalRevenue($totalRevenue);
        $lastMonth->setDateRange($function->createDateRange('30 days'));

        $orderCount = $rep->countLast(['period' => 'lifetime']);
        $unpaidCount = $rep->countLast(['period' => 'lifetime', 'paymentStatus' => PaymentStatus::STATUS_PENDING, 'isCanceled' => false]);
        $unfulfilledCount = $rep->countLast(['period' => 'lifetime', 'orderStatus' => OrderStatus::ORDER_CREATED, 'isCanceled' => false]);
        $totalRevenue = $rep->sumLast(['period' => 'lifetime']);

        $lifetime = new OrdersSummary();
        $lifetime->setOrderCount($orderCount);
        $lifetime->setUnpaidCount($unpaidCount);
        $lifetime->setUnfulfilledCount($unfulfilledCount);
        $lifetime->setTotalRevenue($totalRevenue);

        return $this->render('admin/dashboard.html.twig', [
            'lastDay' => $lastDay,
            'lastWeek' => $lastWeek,
            'lastMonth' => $lastMonth,
            'lifetime' => $lifetime,
        ]);
    }

    /**
     * @Route("/setContentLanguage/{language}", name="content-language")
     */
    public function setContentLanguage(string $language)
    {
        if (!$language) {
            $this->addFlash('success', 'Nem történt nyelvi beállítás...!');
        }

//        dd($this->localization->isSupportedLocale($language));
        $this->localeStorage->setContentLocale($language);
        return $this->redirectToRoute('dashboard');
    }
}