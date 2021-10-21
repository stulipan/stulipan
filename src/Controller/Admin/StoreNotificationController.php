<?php

namespace App\Controller\Admin;

use App\Entity\StoreEmailTemplate;
use App\Form\EmailTemplateFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/settings")
 */
class StoreNotificationController extends AbstractController
{
    /**
     * @IsGranted("ROLE_MANAGE_STORE_NOTIFICATIONS")
     * @Route("/notifications", name="notification-list")
     */
    public function listNotifications()
    {
        $notifications = $this->getDoctrine()
            ->getRepository(StoreEmailTemplate::class)
            ->findAll();

        return $this->render('admin/settings/notification-list.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @IsGranted("ROLE_MANAGE_STORE_NOTIFICATIONS")
     * @Route("/notifications/edit/{id}", name="notification-edit")
     */
    public function editNotification(Request $request, ?StoreEmailTemplate $emailTemplate,
                                      $id = null, TranslatorInterface $translator)
    {
        if (!$emailTemplate) {
            // new
            $form = $this->createForm(EmailTemplateFormType::class);
        } else {
            // edit existing
            $form = $this->createForm(EmailTemplateFormType::class, $emailTemplate);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailTemplate = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($emailTemplate);
            $em->flush();

            $this->addFlash('success', $translator->trans('settings.notification.email-template-saved-successfully'));
            return $this->redirectToRoute('notification-edit', ['id' => $emailTemplate->getId()]);
        }

        return $this->render('admin/settings/notification-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}