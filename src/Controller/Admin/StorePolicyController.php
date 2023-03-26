<?php

namespace App\Controller\Admin;

use App\Entity\StorePolicy;
use App\Form\StorePoliciesFormType;
use App\Model\StorePolicies;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/settings")
 */
class StorePolicyController extends AbstractController
{
    /**
     * @IsGranted("ROLE_MANAGE_STORE_POLICY")
     * @Route("/policy", name="policy-edit")
     */
    public function editPolicyGroup(Request $request, ?StorePolicies $storePolicies)
    {
        $rep = $this->getDoctrine()->getRepository(StorePolicy::class);
        $storePolicies = new StorePolicies(
            $rep->findOneBy(['slug' => StorePolicy::SLUG_TERMS_AND_CONDITIONS] ) ?? new StorePolicy(),
            $rep->findOneBy(['slug' => StorePolicy::SLUG_PRIVACY_POLICY] ) ?? new StorePolicy(),
            $rep->findOneBy(['slug' => StorePolicy::SLUG_SHIPPING_INFORMATION] ) ?? new StorePolicy(),
            $rep->findOneBy(['slug' => StorePolicy::SLUG_RETURN_POLICY] ) ?? new StorePolicy(),
            $rep->findOneBy(['slug' => StorePolicy::SLUG_CONTACT_INFORMATION] ) ?? new StorePolicy(),
            $rep->findOneBy(['slug' => StorePolicy::SLUG_LEGAL_NOTICE] ) ?? new StorePolicy()
        );


        $form = $this->createForm(StorePoliciesFormType::class, $storePolicies);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var StorePolicies $storePolicies */
            $storePolicies = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($storePolicies->getTermsAndConditions());
            $em->persist($storePolicies->getPrivacyPolicy());
            $em->persist($storePolicies->getShippingInformation());
            $em->persist($storePolicies->getReturnPolicy());
            $em->persist($storePolicies->getContactInformation());
            $em->persist($storePolicies->getLegalNotice());
            $em->flush();

            $this->addFlash('success', 'Policy sikeresen elmentve!');
            return $this->redirectToRoute('policy-edit');
        }

        return $this->render('admin/settings/policy-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
