<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Contracts\Translation\TranslatorInterface;

class AnalyticsBreakdown
{
    public const DAY = 'day';
    public const WEEK = 'week';
    public const MONTH = 'month';
    public const YEAR = 'year';
    public const NONE = 'none';

    public const R_SALES_OVER_TIME = 'sales-over-time';

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getBreakdownList(): array
    {
        return [
            $this->translator->trans('analytics.day') => self::DAY,
            $this->translator->trans('analytics.week') => self::WEEK,
            $this->translator->trans('analytics.month') => self::MONTH,
            $this->translator->trans('analytics.year') => self::YEAR,
            $this->translator->trans('analytics.none') => self::NONE
        ];
    }

    public function getDefault()
    {
        return self::DAY;
    }
}