<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    public function onLogoutSuccess(Request $request)
    {
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }
}