<?php

namespace App\Controller\Admin;

use App\Entity\PaymentMethod;
use App\Form\PaymentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/settings")
 */
class SettingsPaymentController extends AbstractController
{
    /**
     * @IsGranted("ROLE_MANAGE_PAYMENT")
     * @Route("/payment", name="payment-list")
     */
    public function listPaymentMethods()
    {
        $payments = $this->getDoctrine()
            ->getRepository(PaymentMethod::class)
            ->findAll();

        return $this->render('admin/settings/payment-methods-list.html.twig', [
            'paymentMethods' => $payments,
        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_PAYMENT")
     * @Route("/payment/edit/{id}", name="payment-edit")
     */
    public function editPaymentMethod(Request $request, ?PaymentMethod $payment, $id = null)
    {
        if (!$payment) {
            /**
             * new Shipping
             */
            $form = $this->createForm(PaymentFormType::class);
        } else {
            /**
             * edit existing Shipping
             */
            $form = $this->createForm(PaymentFormType::class, $payment);
            $title = 'Fizetési mód módosítása';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            $this->addFlash('success', 'Fizetési mód sikeresen elmentve!');

            return $this->redirectToRoute('payment-list');

        }

        return $this->render('admin/settings/payment-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}