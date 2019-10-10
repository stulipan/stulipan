<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Entity\Shipping;
use App\Form\ShippingFormType;
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
    public function showDashboard()
    {
        return $this->render('admin/dashboard.html.twig');
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
            $shipping->setUpdatedAt(new \DateTime('NOW'));
            $shipping->setCreatedAt(new \DateTime('NOW'));

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