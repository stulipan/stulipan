<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;
    
    public function __construct(JWTEncoderInterface $jwtEncoder, UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->jwtEncoder = $jwtEncoder;
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
        return $request->headers->has('Authorization');
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
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );
        
        $token = $extractor->extract($request);
        if (!$token) {
            return false;
        }

        return $token;
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
        $data = $this->jwtEncoder->decode($credentials);
    
        try {
            $data = $this->jwtEncoder->decode($credentials);
        } catch (JWTDecodeFailureException $e) {
            // if you want to, use can use $e->getReason() to find out which of the 3 possible things went wrong
            // and tweak the message accordingly
            // https://github.com/lexik/LexikJWTAuthenticationBundle/blob/05e15967f4dab94c8a75b275692d928a2fbf6d18/Exception/JWTDecodeFailureException.php
        
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }
    
        $email = $data['username'];
    
        // if a User object, checkCredentials() is called
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    /**
     * If we return a User object from getUser(), then Symfony immediately calls checkCredentials(),
     * and passes it the same $credentials and the User object we just returned.
     *
     * This is your opportunity to check to see if the user's password is correct, or any other last, security checks.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case (token authetication)
    
        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
            
            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];
        
        return new JsonResponse($data, Response::HTTP_FORBIDDEN); // 403
    }
    
    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
//        $data = ['message' => 'Authentication Required'];  // you might translate this message
//        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    
        return new JsonResponse(['error' => 'auth required'], Response::HTTP_UNAUTHORIZED); // 401
    }
    
    public function supportsRememberMe()
    {
        return false;
    }
}
