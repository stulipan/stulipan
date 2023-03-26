<?php

namespace App\Controller\Admin;

use App\Entity\ShippingMethod;
use App\Form\ShippingFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

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
            'shippings' => $shippings,
        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_SHIPPING")
     * @Route("/shipping/edit/{id}", name="shipping-edit")
     */
    public function editShipping(Request $request, ?ShippingMethod $shipping, $id = null, TranslatorInterface $translator)
    {
        if (!$shipping) {
             // new Shipping
            $form = $this->createForm(ShippingFormType::class);
        } else {
             // edit existing Shipping
            $form = $this->createForm(ShippingFormType::class, $shipping);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipping = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($shipping);
            $em->flush();

            $this->addFlash('success', $translator->trans('settings.shipping.shipping-saved-successfully'));
            return $this->redirectToRoute('shipping-edit', ['id' => $shipping->getId()]);
        }

        return $this->render('admin/settings/shipping-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}