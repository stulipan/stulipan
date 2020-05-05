<?php

namespace App\Security;

use App\Entity\Order;
use App\Entity\OrderBuilder;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
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
     * @var User
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
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(UserRepository $userRepository, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager,
                                UserPasswordEncoderInterface $passwordEncoder, Security $security, OrderBuilder $orderBuilder,
                                EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
        $this->em = $em;
        $this->orderBuilder = $orderBuilder;
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
//        dd($request->attributes->get('_route') === 'site-login' && $request->isMethod('POST'));
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
//        dd($user->getRoles());
//        dd($this->passwordEncoder->isPasswordValid($user, $credentials['password']));
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
//        dd($request->getPathInfo() === $this->router->generate('admin-login'));

        $this->getItemsFromPreviousOrder();
        $request->getSession()->set('_locale', $request->getLocale());

//        dd($request->request->get('_target_path'));
//        dd($this->getTargetPath($request->getSession(), $providerKey));

//        if ($targetPath = $request->request->get('_target_path')) {
//            return new RedirectResponse($targetPath);
//        }

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

//        return new RedirectResponse($this->router->generate('site-cart'));
//        return null;
        
        return new Response('OK',200);
    }

    /**
     * If login fails, the authenticator calls getLoginUrl() and trying to redirect there.
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('site-login');
    }

    /**
     * Helper function. It is executed right after successful authentication
     * I use it above in onAuthenticationSuccess()
     */
    private function getItemsFromPreviousOrder()
    {
        /**
         * After login, gets items from Customer's previous order and adds them to current order.
         * It's best to be done right after login, than anywhere else in a controller.
         *
         * This way this is executed only once, not needed to put it many controller methods/functions where you would need it executed!
         */
        $this->customer = $this->security->getUser();
        if ($this->customer) {
            $this->orderBuilder->setCustomer($this->customer);
            $prevOrder = $this->em->getRepository(Order::class)
                ->findOneBy(['customer' => $this->customer], ['id' => 'DESC']);   ///??? nem mindig talalja meg
            if ($prevOrder && $prevOrder->hasItems()) {
                foreach ($prevOrder->getItems() as $item) {
                    if (!$this->orderBuilder->containsTheProduct($item->getProduct())) {
                        /**
                         * Csak átrakom az Itemeket a régi Orderből az újba. A régibe nem maradnak meg!
                         * Ez később baj lehet, amikor elhagyott kosár statisztikát csinálnánk és hamisan ott marad
                         */
                        $this->orderBuilder->addItemFromPreviousOrder($item);
                    }
                }
            }
        }
    }

}
