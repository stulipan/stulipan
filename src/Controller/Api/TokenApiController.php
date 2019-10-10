<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * NINCS HASZNALVA!!!
 * Amennyiben hasznalni akarom, akkor a security.yaml-ben ezt kell megadni ketto helyen:
 *      1. api_login > pattern alatt
 *      2. json_login > check_path alatt
 *
 * @Route("/admin")
 */
class TokenApiController extends BaseController
{
    /**
     * @Route("/api/tokens/", name="api-token-createToken", methods={"POST"})
     */
    public function apiCreateToken(Request $request)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $request->getUser()]);
        dd($user);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        
        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->getPassword());
        if (!$isValid) {
            throw new BadCredentialsException();
        }
    
        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'username' => $user->getUsername(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);
    
        return new JsonResponse(['token' => $token]);
    }
}