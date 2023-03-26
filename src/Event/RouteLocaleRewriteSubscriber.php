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
 * Forces any URL to go to a localized version if it exists.
 * Eg.: /admin --> /hu/admin
 *      /login --> /en/admin
 */
class RouteLocaleRewriteSubscriber implements EventSubscriberInterface
{
    private $router;
    private $routeCollection;
    private $defaultLocale;
    private $supportedLocales;
    private $em;

    public function __construct(RouterInterface $router, $defaultLocale, array $supportedLocales, 
                                EntityManagerInterface $entityManager)
    {
        $this->router = $router;
        $this->routeCollection = $router->getRouteCollection();

        $this->defaultLocale = $defaultLocale;  // $defaultLocale comes from services.yaml, the same is with $request->getDefaultLocale();

        // $supportedLocales comes from services.yaml
        $this->supportedLocales = $supportedLocales;
        $this->em = $entityManager;
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

        $currentHost = $request->getHost();
        $currentHost = str_replace('www.','',$currentHost);

        $host = $this->em->getRepository(Host::class)->findOneBy(['name' => $currentHost, 'enabled' => true]);

        $locale = $this->defaultLocale;
        if ($host) {
            $locale = $host->getLanguageCode();
            $request->setLocale($locale);
        }

        // Az alábbi 'try' akkor sikeres, ha a path igy nez ki (nincs benne locale): '/admin', '/login' etc.
        // Ha ilyen path van az URL-ben, akkor megnezni mi a bőngésző default nyelve, és
        //          ha támogatott, akkor '/nyelv/admin' lesz belőle
        //          ha nem támogatott, akkor 'default_nyelv/admin' lesz belőle
        try {
            $matcher->match('/'.$locale.$path);
////             A kikommentelt rész nem kell, mivel a domain határozza meg a nyelvet, nem a bőngésző!
//            $locale = $request->getPreferredLanguage();  // get locale from the user's browser, eg.: "en_US"
//            if ($this->isLocaleSupported($locale)) {
//                $locale = $this->supportedLocales[$locale];  // for "en_US" results in "en", lasd services.yaml
//            } else {
//                $locale = $request->getDefaultLocale();  // gets the locale set in services.yaml >> locale: 'hu'
//            }
            $request->getSession()->set('_locale', $locale);
            $event->setResponse(new RedirectResponse('/'.$locale.$path));
        } catch (ResourceNotFoundException $e) {
//            return;
        } catch (MethodNotAllowedException $e) {
        }

        try {
            $pathMatch = $this->router->match($path);
            $pathLocale = $locale;
            if (isset($pathMatch['_locale'])) {
                $pathLocale = $pathMatch['_locale'];
            }
            // Kapcsold be, hogy rafina.hu >> rafina.hu/hu legyen
//            else {
//                $path = '/'.$locale.$path;
//                $event->setResponse(new RedirectResponse($path));
//            }

            if ($pathLocale != $locale) {
                throw new ResourceNotFoundException(sprintf('No routesssss found for "%s".', $path));
            }
            $request->getSession()->set('_locale', $locale);
        } catch (ResourceNotFoundException $e) {
//            return;
        } catch (MethodNotAllowedException $e) {
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],  // 22 mert a LocaleSubscriber-ben 20 hasznalok, es meg az elott kell betoltse ezt.
        ];
    }
}