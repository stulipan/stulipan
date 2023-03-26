<?php

namespace App\Controller\Admin;

use App\Entity\PaymentMethod;
use App\Form\PaymentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function editPaymentMethod(Request $request, ?PaymentMethod $payment, $id = null, TranslatorInterface $translator)
    {
        if (!$payment) {
            // new Shipping
            $form = $this->createForm(PaymentFormType::class);
        } else {
            // edit existing Shipping
            $form = $this->createForm(PaymentFormType::class, $payment);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            $this->addFlash('success', $translator->trans('settings.payment.payment-saved-successfully'));
            return $this->redirectToRoute('payment-edit', ['id' => $payment->getId()]);
        }

        return $this->render('admin/settings/payment-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}