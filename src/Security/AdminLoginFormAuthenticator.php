<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

class AdminLoginFormAuthenticator extends AbstractFormLoginAuthenticator
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

    public function __construct(UserRepository $userRepository, RouterInterface $router,
                                CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
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
        return $request->attributes->get('_route') === 'admin-login' && $request->isMethod('POST');
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
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
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
        $request->getSession()->set('_locale', $request->getLocale());

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        /**
         * If login URL is admin-login URL, redirects to Dashboard
         */
        if ($request->getPathInfo() === $this->router->generate('admin-login')) {
            return new RedirectResponse($this->router->generate('dashboard'));
        }
        return new RedirectResponse($this->router->generate('site-cart'));
    }

    /**
     * If login fails, the authenticator calls getLoginUrl() and trying to redirect there.
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('admin-login');
    }
}
