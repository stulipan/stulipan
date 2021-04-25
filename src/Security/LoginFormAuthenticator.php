<?php

namespace App\Security;

use App\Entity\Customer;
use App\Services\OrderBuilder;
use App\Services\OrderSessionStorage;
use App\Repository\UserRepository;
use App\Services\AbandonedOrderRetriever;
use App\Services\CustomerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     * @var UserRepository
     */
    private $userRepository;
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
     * @var Security
     */
    private $security;
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;
    /**
     * @param CustomerBuilder $customerBuilder
     */
    private $customerBuilder;
    /**
     * @param AbandonedOrderRetriever $abandonedOrderRetriever
     */
    private $abandonedOrderRetriever;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var RequestStack
     */
    private $requestStack;

    private $orderSession;
    private $translator;

    public function __construct(UserRepository $userRepository, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager,
                                UserPasswordEncoderInterface $passwordEncoder,
                                Security $security, OrderBuilder $orderBuilder, EntityManagerInterface $em,
                                CustomerBuilder $customerBuilder, OrderSessionStorage $orderSession,
                                AbandonedOrderRetriever $abandonedOrderRetriever, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
        $this->em = $em;
        $this->orderBuilder = $orderBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->orderSession = $orderSession;
        $this->abandonedOrderRetriever = $abandonedOrderRetriever;
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
//        dd($request->request->all());
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
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
//        dd($credentials);
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
//        dd($this->userRepository->findOneBy(['email' => $credentials['email']]));
        return $this->userRepository->findOneBy(['email' => $credentials['email']]);
    }

    /**
     * If we return a User object from getUser(), then Symfony immediately calls checkCredentials(),
     * and passes it the same $credentials and the User object we just returned.
     *
     * This is your opportunity to check to see if the user's password is correct, or any other last, security checks.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this->orderSession->add('success', $this->translator->trans('registration.login-success'));
//        $session->getFlashBag()->add('success', $translator->trans('registration.login-success'));

        $orderInSession = $this->orderSession->getOrderById();  // extract Order from session, if any
        $customer = $this->customerBuilder->retrieveCustomer($orderInSession);  // build Customer object
        $abandonedOrder = $this->abandonedOrderRetriever->getOrder();

        if ($customer->getId() === null) {
            $this->em->persist($customer);
            $this->em->flush();
        }

        /**
         * Gets the items from abandoned Order and adds them to the Order in session.
         * Returns the Order in session (with id!).
         */
        if ($orderInSession && $abandonedOrder) {
            if ($abandonedOrder->hasItems()) {
                foreach ($abandonedOrder->getItems() as $item) {
                    if (!$orderInSession->containsTheProduct($item->getProduct())) {
                        $abandonedOrder->removeItem($item);
                        $item->setOrder($orderInSession);
                        $orderInSession->addItem($item);
                    }
                }
            }
            // nem akarjuk az elozo Order teljes tartalmat atmasolni
            // $abandonedOrder->copyPropertyValuesInto($orderInSession);

            $orderInSession->setCustomer($customer);
            $orderInSession->setFirstname($customer->getFirstname());
            $orderInSession->setLastname($customer->getLastname());
            $orderInSession->setEmail($customer->getEmail());
            $orderInSession->setPhone($customer->getPhone());

            if ($orderInSession->getRecipient()) {
                $orderInSession->getRecipient()->setCustomer($customer);
                $this->em->persist($orderInSession->getRecipient());
            }
            if ($orderInSession->getSender()) {
                $orderInSession->getSender()->setCustomer($customer);
                $this->em->persist($orderInSession->getSender());
            }

            $abandonedOrder->setCustomer(null);
            $abandonedOrder->setRecipient(null);
            $abandonedOrder->setSender(null);
            $this->em->remove($abandonedOrder);  // remove abandonedOrder from db

            $this->em->persist($orderInSession);
            $this->em->flush();
        }

        /** If no Order in session, adds the abandonedOrder to the session */
        if (!$orderInSession && $abandonedOrder) {
            $this->orderBuilder->setCurrentOrder($abandonedOrder);
        }

        if ($orderInSession) {
            if ($customer) {
                $orderInSession->setCustomer($customer);
                $orderInSession->setFirstname($customer->getFirstname());
                $orderInSession->setLastname($customer->getLastname());
                $orderInSession->setEmail($customer->getEmail());
                $orderInSession->setPhone($customer->getPhone());

                if ($orderInSession->getRecipient()) {
                    $orderInSession->getRecipient()->setCustomer($customer);
                    $this->em->persist($orderInSession->getRecipient());
                }
                if ($orderInSession->getSender()) {
                    $orderInSession->getSender()->setCustomer($customer);
                    $this->em->persist($orderInSession->getSender());
                }
                $this->em->persist($orderInSession);
                $this->em->flush();
            }
        }

        $request->getSession()->set('_locale', $request->getLocale());

        /**
         * Scenario 1: If there was a target path which triggered login, redirects to target path
         *
         * Scenario 2: In the LoginController\siteLogin() we always set up targetPath = referer URL, so that the user will always go back to the page where he clicked on the login link.
         * For example, if the user is at step2 of checkout and clicks login, he will be redirected back to step2 after successful login.
         */
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }
        /**
         * If login was initiated by going to the Login page, redirects to Homepage
         */
        if ($request->getPathInfo() === $this->router->generate('site-login')) {
//            return new RedirectResponse($request->headers->get('referer') ? $request->headers->get('referer') : $this->router->generate('homepage'));
            return new RedirectResponse($this->router->generate('homepage'));
        }

        return new Response('OK',200);
    }

    /**
     * If login fails, the authenticator calls getLoginUrl() and trying to redirect there.
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('site-login');
    }
}
