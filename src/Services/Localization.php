<?php

namespace App\Services;

use App\Entity\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Exception;

final class Localization
{

    /**
     * @var Locale[]|ArrayCollection|null
     */
    private $locales;

    public function __construct()
    {
        $this->locales = new ArrayCollection();

        $this->locales->add(new Locale('hu','Magyar','Forint', 'HUF', 'Ft'));
        $this->locales->add(new Locale('en','English','Euro', 'EUR', 'HUF'));
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
}