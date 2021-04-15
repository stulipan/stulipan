<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * On an AJAX call with expired session, forces a 403 response.
 * So then this can be caught in the JS script, and handled
 * properly, eg: with a redirect to Login page or error message
 *
 * An expired sessions may occur when user logs out
 * in another browser tab, for example, during the Checkout process.
 */
class AccessDeniedHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // the priority must be greater than the Security HTTP
            // ExceptionListener, to make sure it's called before
            // the default exception listener
//            KernelEvents::EXCEPTION => ['onKernelException', 2]
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        // Ajax is returning login page instead of session expired/access denied
        // Creating a custom handler for ajax
        // more at https://symfony.com/doc/current/security/access_denied_handler.html#customize-the-unauthorized-response

        $request = $event->getRequest();
        if($request->isXmlHttpRequest()){
            $event->setResponse(new Response('Your session has expired_!', Response::HTTP_FORBIDDEN)); //403
            return;
        }
    }
}