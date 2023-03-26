<?php

namespace Stulipan\Traducible\Builder;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LocaleStorage
{
    public const CONTENT_LOCALE = 'content_locale';

    private $session;
    private $defaultLocale;

    public function __construct(SessionInterface $session, ParameterBagInterface $parameterBag) {
        $this->session = $session;
        $this->defaultLocale = $parameterBag->get('stulipan.traducible')['default_content_locale'];
    }

    public function provideCurrentLocale(): ?string
    {
        $currentSession = $this->session;
        if ($currentSession instanceof Session) {
            $currentLocale = $currentSession->get(self::CONTENT_LOCALE);
            if ($currentLocale !== '') {
                return $currentLocale;
            }
        }

        if ($this->defaultLocale !== '') {
            return (string) $this->defaultLocale;
        }

        return null;
    }

    public function setContentLocale($locale): void
    {
        $this->session->set(self::CONTENT_LOCALE, $locale);
    }

    public function getContentLocale(): ?string
    {
        return $this->provideCurrentLocale();
    }
}