<?php

namespace App\Security;

use App\Entity\Customer;
use App\Entity\User;
use App\Services\CheckoutBuilder;
use App\Services\StoreSessionStorage;
use App\Services\AbandonedOrderRetriever;
use App\Services\CustomerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Customer
     */
    private $customer;
    /**
     * @param AbandonedOrderRetriever $abandonedRetriever
     */
    private $abandonedRetriever;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    private $storage;
    private $translator;

    public function __construct(EntityManagerInterface       $em, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager,
                                UserPasswordEncoderInterface $passwordEncoder, CheckoutBuilder $checkoutBuilder,
                                CustomerBuilder              $customerBuilder, StoreSessionStorage $storage,
                                AbandonedOrderRetriever      $abandonedRetriever, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->storage = $storage;
        $this->abandonedRetriever = $abandonedRetriever;
        $this->translator = $translator;
    }

    /**
     * The first method - supports() - is called on every request. Our job is simple: to return true
     * if this request contains authentication info that this authenticator knows how to process.
     * And if not, to return false.
     *
     * In this case, when we submit the login form, it POSTs to /login or /admin/login. So, our
     * authenticator should only try to authenticate the user in that exact situation.
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'site-login' && $request->isMethod('POST');
    }

    /**
     * If we return true from supports(), Symfony will immediately call getCredentials().
     * Back to work! Our job in getCredentials() is simple: to read our authentication
     * credentials off of the request and return them. In this case, we'll return the email and password.
     *
     * But, if this were an API token authenticator, we would return that token.
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);

        return $credentials;
    }

    /**
     * After we return from getCredentials(), Symfony will immediately call getUser() and pass
     * $credentials array as the first argument.
     *
     * Our job in getUser() is to use these $credentials to return a User object, or null
     * if the user isn't found. Because we're storing our users in the database, we need to query
     * for the user via their email.
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }
        return $user;
    }

    /**
     * If we return a User object from getUser(), then Symfony immediately calls checkCredentials(),
     * and passes it the same $credentials and the User object we just returned.
     *
     * This is your opportunity to check to see if the user's password is correct, or any other last, security checks.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $isPasswordValid = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
        if (!$isPasswordValid) {
            throw new CustomUserMessageAuthenticationException('Password is incorrect.');
        }
        return $isPasswordValid;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this->storage->add('success', $this->translator->trans('registration.login-success'));   /// ???

        /** @var User $user */
        $user = $token->getUser();
        $checkoutInSession = $this->storage->getCheckoutById();  // extract Checkout from session, if any

        if ($checkoutInSession) {
            $customerInSession = $checkoutInSession->getCustomer();
            $customerInUser = $user->getCustomer();

            if ($customerInSession && $customerInUser) {
               if ($customerInSession === $customerInUser) {
                   // do nothing
               }
               else {
                   $checkoutInSession->setCustomer(null);
                   if ($customerInSession->getCheckouts()->isEmpty()) {
                       $this->em->remove($customerInSession);  // remove unused Customer
                   }
                   $checkoutInSession->setCustomer($customerInUser);  // update Customer in current Checkout
                   $checkoutInSession->setEmail($customerInUser->getEmail());
//                   $checkoutInSession->setPhone($customerInUser->getPhone());
                   $this->em->persist($checkoutInSession);
                   $this->em->flush();

                   $customerInSession = $customerInUser;
               }
            }

            if ($customerInSession && !$customerInUser) {
                $customerInSession->setUser($user);
                $checkoutInSession->setEmail($customerInSession->getEmail());
//                $checkoutInSession->setPhone($customerInSession->getPhone());
                $this->em->persist($customerInSession);
                $this->em->persist($checkoutInSession);
                $this->em->flush();
            }

            if (!$customerInSession && $customerInUser) {
                $checkoutInSession->setCustomer($customerInUser);
                $checkoutInSession->setEmail($customerInUser->getEmail());
                $this->em->persist($checkoutInSession);
                $this->em->flush();
            }

            if (!$customerInSession && !$customerInUser) {
                $customer = new Customer();
                $customer->setEmail($user->getEmail());
                $customer->setFirstname($user->getFirstname());
                $customer->setLastname($user->getLastname());
                $customer->setPhone($user->getPhone());
                $customer->setUser($user);
                $checkoutInSession->setCustomer($customer);
                $checkoutInSession->setEmail($customer->getEmail());
//                $checkoutInSession->setPhone($customer->getPhone());
                $this->em->persist($customer);
                $this->em->persist($checkoutInSession);
                $this->em->flush();
            }

//            if ($user->getCustomer()) {
//                if ($customerInSession) {
//                    $checkoutInSession->setCustomer(null);
//
//                    $this->em->remove($customerInSession);  // remove unused Customer
//                }
//
//                $checkoutInSession->setCustomer($user->getCustomer());  // update Customer in current Checkout
//                $checkoutInSession->setEmail($user->getCustomer()->getEmail());
//                $checkoutInSession->setPhone($user->getCustomer()->getPhone());
//
//                $this->em->persist($checkoutInSession);
//                $this->em->flush();
//
//                $customer = $user->getCustomer();
//            }
//            else {
//                if ($customer) {
//                    $customer->setUser($user);
//                    $checkoutInSession->setEmail($customer->getEmail());
//                    $checkoutInSession->setPhone($customer->getPhone());
//                    $this->em->persist($customer);
//                    $this->em->persist($checkoutInSession);
//                    $this->em->flush();
//                } else {
//                    $customer = new Customer();
//                    $customer->setEmail($user->getEmail());
//                    $customer->setFirstname($user->getFirstname());
//                    $customer->setLastname($user->getLastname());
//                    $customer->setPhone($user->getPhone());
//                    $customer->setUser($user);
//                    $checkoutInSession->setCustomer($customer);
//                    $checkoutInSession->setEmail($customer->getEmail());
//                    $checkoutInSession->setPhone($customer->getPhone());
//                    $this->em->persist($customer);
//                    $this->em->persist($checkoutInSession);
//                    $this->em->flush();
//                }
//            }

            if ($checkoutInSession->getRecipient()) {
                $checkoutInSession->getRecipient()->setUser($user);
                $user->getRecipients()->add($checkoutInSession->getRecipient());
                $this->em->persist($checkoutInSession->getRecipient());
                $this->em->persist($user);
                $this->em->flush();
            }
            if ($checkoutInSession->getSender()) {
                $checkoutInSession->getSender()->setUser($user);
                $user->getSenders()->add($checkoutInSession->getSender());
                $this->em->persist($checkoutInSession->getSender());
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        $abandonedCheckout = $this->abandonedRetriever->getCheckout($user, $checkoutInSession);
//        dd($abandonedCheckout);

        // Gets the items from abandoned Order and adds them to the Order in session.
        // Returns the Order in session (with id!).
        if ($checkoutInSession && $abandonedCheckout) {
            if ($abandonedCheckout->hasItems()) {
                foreach ($abandonedCheckout->getItems() as $item) {
                    if (!$checkoutInSession->containsTheProduct($item->getProduct())) {
                        $abandonedCheckout->removeItem($item);
                        $item->setCheckout($checkoutInSession);
                        $checkoutInSession->addItem($item);
                    }
                }
                foreach ($abandonedCheckout->getCart()->getItems() as $item) {
                    if (!$checkoutInSession->getCart()->containsTheProduct($item->getProduct())) {
                        $abandonedCheckout->getCart()->removeItem($item);
                        $item->setCart($checkoutInSession->getCart());
                        $checkoutInSession->getCart()->addItem($item);
                    }
                }
            }

            $abandonedCheckout->setCustomer(null); // remove Customer
            $prevRecipient = $abandonedCheckout->getRecipient();
            $abandonedCheckout->setRecipient(null);  // remove Recipient
            if ($prevRecipient && $prevRecipient->getUser() === null) {
                $this->em->remove($prevRecipient);
            }
            $prevSender = $abandonedCheckout->getSender();  // remove Sender
            $abandonedCheckout->setSender(null);
            if ($prevSender && $prevSender->getUser() === null) {
                $this->em->remove($prevSender);
            }
            $this->em->remove($abandonedCheckout);  // remove abandonedOrder from db

            $this->em->persist($checkoutInSession->getCart());
            $this->em->persist($checkoutInSession);
            $this->em->flush();
        }

        // If no Checkout in session, adds the abandonedCheckout to the session
        if (!$checkoutInSession && $abandonedCheckout) {
            $this->storage->set(StoreSessionStorage::CART_ID, $abandonedCheckout->getCart()->getId());
            $this->storage->set(StoreSessionStorage::CHECKOUT_ID, $abandonedCheckout->getId());
        }

        $request->getSession()->set('_locale', $request->getLocale());

        // Scenario 1: If there was a target path which triggered login, redirects to target path
        // Scenario 2: In the LoginController\siteLogin() we always set up targetPath = referer URL, so that the user will always go back to the page where he clicked on the login link.
        // For example, if the user is at step2 of checkout and clicks login, he will be redirected back to step2 after successful login.
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }
        // If login was initiated by going to the Login page, redirects to Homepage
        if ($request->getPathInfo() === $this->router->generate('site-login')) {
            return new RedirectResponse($this->router->generate('homepage'));
        }

//        return new Response('OK',200);
        return new RedirectResponse($this->router->generate('homepage'));
    }

    /**
     * If login fails, the authenticator calls getLoginUrl() and trying to redirect there.
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('site-login');
    }
}
