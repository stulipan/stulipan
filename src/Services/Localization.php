<?php

namespace App\Services;

use App\Model\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class Localization
{
    public const DATE_FORMAT_DEFAULT = [
        'hu' => 'Y-m-d',
        'en' => 'm/d/Y',
    ];
    public const TIME_FORMAT_DEFAULT = [
        'hu' => 'H:i',
        'en' => 'H:i',
    ];

    public const SLUGIFY_RULES = ['default', 'hungarian'];


    /**
     * @var Locale[]|ArrayCollection|null
     */
    private $locales;

    private $session;

    private $defaultLocale;

    public function __construct(StoreSettings $settings, SessionInterface $session, $defaultLocale)
    {
        $this->session = $session;

        $this->locales = new ArrayCollection();
        $this->locales->add(new Locale(
            'hu',
            'Magyar',
            'Forint',
            'HUF',
            'Ft',
            $settings->getDateFormat() ?: self::DATE_FORMAT_DEFAULT['hu'],
            $settings->getTimeFormat() ?: self::TIME_FORMAT_DEFAULT['hu'],
        ));
        $this->locales->add(new Locale(
            'en',
            'English',
            'Euro',
            'EUR',
            'HUF',
            $settings->getDateFormat() ?: self::DATE_FORMAT_DEFAULT['en'],
            $settings->getTimeFormat() ?: self::TIME_FORMAT_DEFAULT['en'],
        ));

        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return Locale[]|Collection|null
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param string $code
     * @return Locale
     * @throws
     */
    public function getLocale(string $code): ?Locale
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('code',$code));

        if ($this->locales->matching($criteria)->isEmpty()) {
            throw new Exception('HIBA: Localization class-ban >> a getLocale() nem dobott találatot!');
        }
        return $this->locales->matching($criteria)->first();
    }

    /**
     * Returns the current Locale that is in use on the website
     *
     * @return Locale|null
     * @throws Exception
     */
    public function getCurrentLocale(): ?Locale
    {
        $code = $this->session->get('_locale');
        if (!$code) {
            $code = $this->defaultLocale;
        }
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('code',$code));

        if ($this->locales->matching($criteria)->isEmpty()) {
            throw new Exception('HIBA: Localization class-ban >> a getCurrentLocale() nem dobott találatot!');
        }
        return $this->locales->matching($criteria)->first();
    }
}