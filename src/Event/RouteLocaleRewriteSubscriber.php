<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Forces any URL to go to a localized version if it exists.
 * Eg.: /admin/dashboard --> /hu/admin/dashboard
 */
class RouteLocaleRewriteSubscriber implements EventSubscriberInterface
{
    private $router;
    private $routeCollection;
    private $defaultLocale;
    private $supportedLocales;
    private $localeRouteParam;

    public function __construct(RouterInterface $router, $defaultLocale, array $supportedLocales, $localeRouteParam = '_locale')
    {
        $this->router = $router;
        $this->routeCollection = $router->getRouteCollection();

        // $defaultLocale comes from services.yaml
        $this->defaultLocale = $defaultLocale;

        // $supportedLocales comes from services.yaml
        $this->supportedLocales = $supportedLocales;
        $this->localeRouteParam = $localeRouteParam;
    }

    public function isLocaleSupported($locale)
    {
        return array_key_exists($locale, $this->supportedLocales);
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();
        $path = rtrim($path, '/');  // remove trailing '/' E.g: /admin/dashboard/ --> /admin/dashboard

        $context = new RequestContext('/');
        $matcher = new UrlMatcher($this->routeCollection, $context);

        try {
            $matcher->match('/'.$this->defaultLocale.$path);
            $locale = $request->getPreferredLanguage();  // get locale from the user's browser, eg.: "en_US"

            if ($this->isLocaleSupported($locale)) {
                $locale = $this->supportedLocales[$locale];  // for "en_US" results in "en"
            } else {
                $locale = $request->getDefaultLocale();  // gets the locale set in services.yaml >> locale: 'hu'
            }
            $event->setResponse(new RedirectResponse('/'.$locale.$path));

        } catch (ResourceNotFoundException $e) {
        } catch (MethodNotAllowedException $e) {
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 19]],  // 19 mert a LocaleSubscriber-ben 20 hasznalok, es meg az elott kell betoltse ezt.
        ];
    }
}