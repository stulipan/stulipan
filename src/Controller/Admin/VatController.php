<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a szamla adatbázistáblából

namespace App\Controller\Admin;

use App\Entity\VatRate;
use App\Entity\VatValue;
use App\Form\VatRateFormType;
use App\Form\VatValueFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_MANAGE_VAT")
 * @Route("/admin")
 */
class VatController extends AbstractController
{
    /**
     * @Route("/vat-value/edit/{id}", name="vat-value-edit")
     */
    public function editVatValue(Request $request, ?VatValue $vatValue, $id = null)
    {
        if (!$vatValue) {
            // new
            $form = $this->createForm(VatValueFormType::class);
            $title = 'ÁFA értéke';
        } else {
            // edit existing
            $form = $this->createForm(VatValueFormType::class, $vatValue);
            $title = 'ÁFA értéke';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vatValue = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vatValue);
            $entityManager->flush();

            $this->addFlash('success', 'ÁFA értéke sikeresen elmentve!');

            return $this->redirectToRoute('vat-value-list');
        }

        return $this->render('admin/settings/vat-value-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    /**
     * @Route("/vat-value/", name="vat-value-list")
     */
    public function listVatValues()
    {
        $vatValues = $this->getDoctrine()->getRepository(VatValue::class)->findAll();
        $title = 'ÁFA értékek';
//        if (!$vatValues) {
//            throw $this->createNotFoundException(
//                'Nem talált egy ÁFÁ-t sem!'
//            );
//        }

        return $this->render('admin/settings/vat-value-list.html.twig', [
            'vatValues' => $vatValues,
            'title' => $title,
        ]);
    }

    /**
     * @Route("/vat-rate/edit/{id}", name="vat-rate-edit")
     */
    public function editVatRate(Request $request, ?VatRate $vatRate, $id = null)
    {
        if (!$vatRate) {
            // new
            $form = $this->createForm(VatRateFormType::class);
            $title = 'Új ÁFA típus';
        } else {
            // edit existing
            $form = $this->createForm(VatRateFormType::class, $vatRate);
            $title = 'ÁFA típus módosítása';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vatRate = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vatRate);
            $entityManager->flush();

            $this->addFlash('success', 'ÁFA típus sikeresen elmentve!');

            return $this->redirectToRoute('vat-rate-list');
        }

        return $this->render('admin/settings/vat-rate-edit.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }
    
    /**
     * @Route("/vat-rate/", name="vat-rate-list")
     */
    public function listVatRates()
    {
        $vatRates = $this->getDoctrine()->getRepository(VatRate::class)->findAll();
        $title = 'ÁFA típusok';
//        if (!$vatRates) {
//            throw $this->createNotFoundException(
//                'Nem talált egy ÁFA típust sem!'
//            );
//        }

        return $this->render('admin/settings/vat-rate-list.html.twig', [
            'vatRates' => $vatRates,
            'title' => $title,
        ]);
    }
    
}