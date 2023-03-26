<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Host;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * !!! NINCS HASZNALATBAN
 * Problema:
 *          - mukodik a redirect (stulipan.com >> stulipan.com/en)
 *          - de valami hibat ad a twig templateben >> render(controller()) !!!
 */
class RouteLocaleByDomain implements EventSubscriberInterface
{
    private $router;
    private $routeCollection;
    private $defaultLocale;
    private $em;

    public function __construct(RouterInterface $router, $defaultLocale,
                                EntityManagerInterface $entityManager)
    {
        $this->router = $router;
        $this->routeCollection = $router->getRouteCollection();

        $this->defaultLocale = $defaultLocale;  // $defaultLocale comes from services.yaml, the same is with $request->getDefaultLocale();
        $this->em = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (true) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();
        $path = rtrim($path, '/');  // remove trailing '/' E.g: /hu/termekek/ --> /hu/termekek
        $locale = $this->defaultLocale;

        $context = new RequestContext('/');
        $matcher = new UrlMatcher($this->routeCollection, $context);

        // TEENDŐ: itt eszlelni tudom a host-ot, es ha letezo hostrol van szo, akkor hozza kell csak parositani a host-nyelvet
        // Tehat, ha stulipan.com-ot uti be, akkor a nyelv = 'en'
        // Ha stulipan.com/hu/termekek, akkor a nyelv = 'en' es az URL hibat dob
        // Ha stulipan.com/en/product, akkor a nyelv = 'en' es az URL rendben van

        $hosts = $this->em->getRepository(Host::class)->findBy(['enabled' => true]);
        $currentHost = $request->getHost();
        $currentHost = str_replace('www.','',$currentHost);

        foreach ($hosts as $host) {
            if ($currentHost == $host->getName()) {
                $locale = $host->getLanguageCode();
                break;
            }
        }

        try {
//            $pathMatch = $matcher->match($path);
//            dd($pathMatch);
            $pathMatch = $this->router->match($path);
            $pathLocale = $locale;
            if (isset($pathMatch['_locale'])) {
                $pathLocale = $pathMatch['_locale'];
            } else {
                $path = '/'.$locale.$path;
                $event->setResponse(new RedirectResponse($path));
            }

            if ($pathLocale != $locale) {
                throw new ResourceNotFoundException(sprintf('No routesssss found for "%s".', $path));
            }
        } catch (ResourceNotFoundException $ee) {
        } catch (MethodNotAllowedException $ee) {
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 21]],  // 19 mert a LocaleSubscriber-ben 20 hasznalok, es meg az elott kell betoltse ezt.
        ];
    }
}