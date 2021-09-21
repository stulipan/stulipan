<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminLoginController extends AbstractController
{
    /**
     * @Route("/admin/login", name="admin-login")
     */
    public function adminLogin(AuthenticationUtils $authenticationUtils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('dashboard');
        }
        $error = $authenticationUtils->getLastAuthenticationError(); // get the login error if there is one
        $lastUsername = $authenticationUtils->getLastUsername(); // last username entered by the user

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/admin/logout", name="admin-logout")
     */
    public function adminLogout()
    {
    }

}