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
 * @Route("/admin/settings")
 */
class SettingsShippingController extends AbstractController
{
    /**
     * @IsGranted("ROLE_MANAGE_SHIPPING")
     * @Route("/shipping", name="shipping-list")
     */
    public function listShippingMethods()
    {
        $shippings = $this->getDoctrine()
            ->getRepository(ShippingMethod::class)
            ->findAll();

        return $this->render('admin/settings/shipping-methods-list.html.twig', [
            'title' => 'Szállítási és fizetési módok',
            'shippings' => $shippings,
        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_SHIPPING")
     * @Route("/shipping/edit/{id}", name="shipping-edit")
     */
    public function editShipping(Request $request, ?ShippingMethod $shipping, $id = null)
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($shipping);
            $em->flush();

            $this->addFlash('success', 'Szállítási mód sikeresen elmentve!');

            return $this->redirectToRoute('shipping-list');

        }

        return $this->render('admin/settings/shipping-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
}