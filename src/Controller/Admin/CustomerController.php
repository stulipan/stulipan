<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Entity\Shipping;
use App\Entity\User;
use App\Form\ShippingFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_MANAGE_CUSTOMERS")
 * @Route("/admin")
 */
class CustomerController extends AbstractController
{

    /**
     * @Route("/customer/{id}", name="customer-show")
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
        foreach ($user->getRealOrders() as $o => $order) {
            $totalRevenue += $order->getSummary()->getTotalAmountToPay();
        }

        return $this->render('admin/customer-profile-show.html.twig', [
            'title' => 'Vásárlói adatlap',
            'user' => $user,
            'orders' => $user->getRealOrders(),
//            'shippings' => $shippings,
//            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
        ]);
    }


}