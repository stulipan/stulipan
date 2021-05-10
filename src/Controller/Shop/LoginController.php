<?php

namespace App\Controller\Shop;

use App\Entity\User;
use App\Form\UserRegistration\ForgottenPasswordFormType;
use App\Form\UserRegistration\UserRegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    use TargetPathTrait;
    
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

        /**
         * TargetPathTrait segitsegevel el tudom menteni session-be a referer URL-t.
         * When initiating login by coming to the Login page, we save the referer URL into the targetPath.
         * Why? Because targetPath existence will be checked during the authentication process in App\Security\LoginFormAuthenticator,
         * thus the authenticator will know where to bring back the user after successful login.
         *
         * Fontos, hogy sikeres login utan toroljem a session-bol!  >> legalabbis ez az elmelet. MOST NINCS TOROLVE!!
         */
        $this->saveTargetPath($request->getSession(), 'main', $request->headers->get('referer') ? $request->headers->get('referer') : '');
        
        return $this->render('webshop/user/user-login-register.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * @Route("/register", name="site-register")
     */
    public function siteRegister(Request $request, UserPasswordEncoderInterface $encoder,
                                 GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator,
                                 TranslatorInterface $translator)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }
        $form = $this->createForm(UserRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = new User();
            $user->setEmail($data->getEmail());
            $user->setFirstname($data->getFirstname());
            $user->setLastname($data->getLastname());
            $user->setUsername('' != $data->getUsername() && null != $data->getUsername() ? $data->getUsername() : $data->getEmail());
            $password = $encoder->encodePassword($user, $form->get('password')->getData());
            $user->setPassword($password);
            $roles[] = 'ROLE_USER';
            $user->setRoles($roles);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('registration.registration-success'));
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
    
        /**
         * Renders the form with errors
         * If AJAX request and the form was submitted, renders the form, fills it with data and validation errors!
         */
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $html = $this->renderView('webshop/cart/registration-form-duringCheckout.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
            return new Response($html,400);
        }

        return $this->render('webshop/user/user-register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}