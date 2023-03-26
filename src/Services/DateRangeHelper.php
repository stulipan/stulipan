<?php

namespace App\Services;

use App\Entity\DateRange;
use Cocur\Slugify\Slugify;
use DateTime;

class DateRangeHelper
{
    private $localization;

    public function __construct(Localization $localization)
    {
        $this->localization = $localization;
    }

    public function split(string $dateRangeString = null): ?DateRange
    {
        if ($dateRangeString === null) {
            return null;
        }

        $splitPieces = explode(" - ", $dateRangeString);
        $start = $splitPieces[0];
        $end = $splitPieces[1];
        $format = $this->localization->getCurrentLocale()->getDateFormat();

        $dateRange = new DateRange();
        if (!isset($start) or $start === null or $start == '') {
        } else {
            $dateRange->setStart(DateTime::createFromFormat($format, $start));
        }
        if (!isset($end) or $end === null or $end == '') {
        } else {
            $dateRange->setEnd(DateTime::createFromFormat($format, $end));
        }
        return $dateRange;
    }
    public function diff(DateRange $dateRange = null)
    {
        return $dateRange->getEnd()->diff($dateRange->getStart());
    }

    public function diffOld(string $dateRangeString = null)
    {
        $dateRange = $this->split($dateRangeString);
        $end = $dateRange->getEnd();
        $start = $dateRange->getStart();

        return $dateRange->getEnd()->diff($dateRange->getStart());
    }

    public function toDateRangeString(DateRange $dateRange)
    {
        $start = $dateRange->getStart()->format($this->localization->getCurrentLocale()->getDateFormat());
        $end = $dateRange->getEnd()->format($this->localization->getCurrentLocale()->getDateFormat());
        return $start.' - '.$end;
    }
}