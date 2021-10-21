<?php

namespace App\Controller\Shop;

use App\Entity\StoreEmailTemplate;
use App\Entity\User;
use App\Form\UserRegistration\ChangePasswordFormType;
use App\Form\UserRegistration\ResetPasswordRequestFormType;
use App\Services\StoreSettings;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @Route({
 *     "hu": "/elfelejtett-jelszo",
 *     "en": "/reset-password",
 *      })
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    private $storeSettings;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper, StoreSettings $storeSettings)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->storeSettings = $storeSettings;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="site-reset-password")
     */
    public function requestEmail(Request $request, MailerInterface $mailer): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }

        return $this->render('webshop/user/forgotten-password/email-form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="site-reset-password-checkEmail")
     */
    public function checkEmail(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        // We prevent users from directly accessing this page
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            return $this->redirectToRoute('site-reset-password');
        }

        return $this->render('webshop/user/forgotten-password/check-email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="site-reset-password-resetPassword")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null, TranslatorInterface $translator): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('site-reset-password-resetPassword');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
//            $this->addFlash('reset_password_error', sprintf(
//                'There was a problem validating your reset request - %s',
//                $e->getReason()
//            ));
            $this->addFlash('reset_password_error', $translator->trans('registration.forgot-password.reset-link-expired'));

            return $this->redirectToRoute('site-reset-password');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode the plain password, and set it.
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('webshop/user/forgotten-password/reset-form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('site-reset-password-checkEmail');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'site-reset-password'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return $this->redirectToRoute('site-reset-password-checkEmail');
        }

        $template = $this->getDoctrine()->getRepository(StoreEmailTemplate::class)->findOneBy(['slug' => 'forgotten-password']);

        $loader = new ArrayLoader([
            'forgotten-password' => $template->getBody(),
        ]);
        $twig = new Environment($loader);

        $html = $twig->render('forgotten-password', [
            'subject' => $template->getSubject(),
            'urlResetPassword' => $this->generateUrl('site-reset-password-resetPassword', ['token' => $resetToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address(
                $this->storeSettings->get('notifications.sender-email'),
                $this->storeSettings->get('notifications.sender-name')
            ))
            ->to($user->getEmail())
//            ->subject('Elfelejtett jelszó')
//            ->htmlTemplate('webshop/emails/forgotten-password.html.twig')
            ->subject($template->getSubject())
            ->html($html)
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('site-reset-password-checkEmail');
    }
}
