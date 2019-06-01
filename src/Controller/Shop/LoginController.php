<?php

namespace App\Controller\Shop;

use App\Entity\User;
use App\Form\UserRegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @Route("/logout", name="logout")
     */
    public function siteLogout()
    {
    }

    /**
     * Displays Login and Register forms, but only handles login
     *
     * @Route("/login", name="site-login")
     */
    public function siteLogin(AuthenticationUtils $authenticationUtils, Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }
        $error = $authenticationUtils->getLastAuthenticationError(); // get the login error if there is one
        $lastUsername = $authenticationUtils->getLastUsername(); // last username entered by the user

        $registrationForm = $this->createForm(UserRegistrationFormType::class);

        return $this->render('webshop/site/user-login-register.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'title' => 'Bejelentkezés',
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * @Route("/register", name="site-register")
     */
    public function siteRegister(Request $request, UserPasswordEncoderInterface $encoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator) //AuthenticationUtils $authenticationUtils,
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }
        $registrationForm = $this->createForm(UserRegistrationFormType::class);
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $data = $registrationForm->getData();
            $user = new User();
            $user->setEmail($data->getEmail());
            $user->setFirstname($data->getFirstname());
            $user->setLastname($data->getLastname());
            $user->setUsername('' != $data->getUsername() && null != $data->getUsername() ? $data->getUsername() : $data->getEmail());
            $password = $encoder->encodePassword($user, $registrationForm->get('password')->getData());
            $user->setPassword($password);
            $roles[] = 'ROLE_USER';
            $user->setRoles($roles);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            /**
             * Instead of redirecting to a normal route use return $guardHandler->authenticateUserAndHandleSuccess()
             * This needs a few arguments: the $user that's being logged in, the $request object,
             * the authenticator - $formAuthenticator and the "provider key". That's just the name of your firewall: main
             */
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main'     // the "provider key". That's just the name of your firewall: main
            );
        }

        return $this->render('webshop/site/user-register.html.twig', [
            'title' => 'Fiók létrehozása',
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

}