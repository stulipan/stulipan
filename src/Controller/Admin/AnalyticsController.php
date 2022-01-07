<?php

namespace App\Controller\Admin;

use App\Entity\DateRange;
use App\Entity\Order;
use App\Form\AnalyticsFilterType;
use App\Model\OrdersSummary;
use App\Services\AnalyticsBreakdown;
use App\Services\DateRangeHelper;
use App\Services\Localization;
use App\Services\StoreSettings;
use App\Twig\AppExtension;
use DateTime;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class AnalyticsController extends AbstractController
{
    private $dateRangeHelper;
    private $ab;
    private $defaultDateFormat;
    private $translator;
    private $appExtension;

    public function __construct(DateRangeHelper $dateRangeHelper, AnalyticsBreakdown $analyticsBreakdown,
                                Localization $localization, TranslatorInterface $translator,
                                AppExtension $appExtension)
    {
        $this->dateRangeHelper = $dateRangeHelper;
        $this->ab = $analyticsBreakdown;
        $this->defaultDateFormat = $localization->getCurrentLocale()->getDateFormat();
        $this->translator = $translator;
        $this->appExtension = $appExtension;
    }


    /**
     * @Route("/analytics/sales-over-time", name="analytics-sales-over-time")
     */
    public function showSalesOverTime(Request $request, $page = 1, StoreSettings $settings)
    {
        $dateRange = $request->query->get('dateRange');
        $groupBy = $request->query->get('groupBy');

        $rep = $this->getDoctrine()->getRepository(Order::class);
        if ($dateRange == null) {
            // Start date is when first order was placed
            // Then build range
            $ordersPlaced = $rep->findBy(['status' => !null], ['postedAt' => 'ASC']);
            if (count($ordersPlaced) > 0) {
                $start = $ordersPlaced[0]->getPostedAt();
            }
            if (!isset($start)) {
                $start = new DateTime();
            }
            $end = new DateTime();
            $range = new DateRange($start, $end);
        } else {
            $range = $this->dateRangeHelper->split($dateRange);
        }
//        if ($groupBy == null) {
//            $groupBy = $this->ab->getDefault();
//        }

        $filterTags = [];
        $urlParams = [];
        $data = $filterTags;

        if ($dateRange) {
            $filterTags['dateRange'] = $this->translator->trans('analytics.date-range', ['{{ value }}' => $dateRange]);
            $data['dateRange'] = $dateRange;
            $urlParams['dateRange'] = $dateRange;
        }
        if ($groupBy) {
            $filterTags['groupBy'] = $this->translator->trans('analytics.breakdown', ['{{ value }}' => $this->getGroupByString($groupBy)]);
            $data['groupBy'] = $groupBy;
            $urlParams['groupBy'] = $groupBy;
        }

        $filterUrls = [];
        foreach ($filterTags as $key => $value) {
            // remove the current filter from the urlParams
            $shortlist = array_diff_key($urlParams,[$key => '']);

            // generate the URL with the remaining filters
            $filterUrls[$key] = $this->generateUrl('analytics-sales-over-time',[
                'dateRange' => isset($shortlist['dateRange']) ? $shortlist['dateRange'] : null,
                'groupBy' => isset($shortlist['groupBy']) ? $shortlist['groupBy'] : null,
            ]);
        }

        if ($dateRange) {
            $orderCount = $rep->countLast(['period' => 'dateRange', 'dateRange' => $dateRange]);
            $totalRevenue = $rep->sumLast(['period' => 'dateRange', 'dateRange' => $dateRange]);
        } else {
            $orderCount = $rep->countLast(['period' => 'lifetime']);
            $totalRevenue = $rep->sumLast(['period' => 'lifetime']);
        }

        $currentPeriod = new OrdersSummary();
        $currentPeriod->setOrderCount($orderCount);
        $currentPeriod->setTotalRevenue($totalRevenue);

        $filterForm = $this->createForm(AnalyticsFilterType::class, $data);

        // DATA IN THE LIST
        $sales = [];
        $startR = clone $range->getStart();
        $endR = clone $range->getEnd();
        $numberOfBreakdowns = $this->getNumberOfBreakdowns($groupBy, $range);
        if ($numberOfBreakdowns >= 1) {
            for ($i = 1; $i<=$numberOfBreakdowns; $i++) {
                switch ($groupBy) {
                    case AnalyticsBreakdown::DAY:
                        $start = clone $startR;
                        $start = $start->modify('+'.($i-1).' '.($i-1 == 1 ? 'day' : 'days'));
                        $currentRange = new DateRange($start, $start);
                        $currentBreakdown = $start->format($this->defaultDateFormat);
                        break;
                    case AnalyticsBreakdown::WEEK:
                        if ($i == 1) {
                            $start = clone $startR;
                        }
                        if ($i > 1) {
                            $start = clone $startNew;
                        }
                        $monday = (clone $start)->modify('Monday this week');
                        $sunday = (clone $start)->modify('Sunday this week');
                        $end = $sunday;

                        // Verify if 'end of week' is smaller than 'end of range'
                        $interval = $end->diff($endR);

                        // if diff is negative days
                        // then 'end of range' is before 'end of week'
                        if ($interval->days > 0 and $interval->invert == 1) {
                            $end = clone $endR;
                        }
                        if ($interval->days == 0) {
                            $end = clone $endR;
                        }
                        // if diff is positive days
                        // then 'end of range' is after 'end of week' ==> do nothing
                        if ($interval->days > 0 and $interval->invert == 0) {

                        }
                        $currentRange = new DateRange($start, $end);
                        $currentBreakdown = $this->appExtension->formatLocalizedDate($monday, 'W, M j');

                        $startNew = (clone $end)->modify('+1 day');
                        $endNew = (clone $startNew)->modify('+6 days');
                        break;
                    case AnalyticsBreakdown::MONTH:
                        if ($i == 1) {
                            $start = clone $startR;
                            $end = new DateTime($start->format('Y-m-t'));
//                            $end = (clone $start)->modify('last day of this month'); // Create "end of month" date
                        }
                        if ($i > 1) {
                            $start = clone $startNew;
                            $end = clone $endNew;
                        }

                        // Verify if 'end of month' is smaller than 'end of range'
                        $interval = $end->diff($endR);
                        // if diff is negative days
                        // then 'end of range' is before 'end of month'
                        if ($interval->days > 0 and $interval->invert == 1) {
                            $end = clone $endR;
                        }
                        if ($interval->days == 0) {
                            $end = clone $endR;
                        }
                        // if diff is positive days
                        // then 'end of range' is after 'end of month'
                        if ($interval->days > 0 and $interval->invert == 0) {

                        }
                        $currentBreakdown = $this->appExtension->formatLocalizedDate($end, 'M Y');
                        $currentRange = new DateRange($start, $end);

                        $startNew = (clone $end)->modify('+1 day');
//                        $endNew = new \DateTime($startNew->format('Y-m-t'));
                        $endNew = (clone $startNew)->modify('last day of this month');
                        break;
                    case AnalyticsBreakdown::YEAR:
                        if ($i == 1) {
                            $start = clone $startR;
                            $end = (clone $start)->modify('last day of December this year');
                        }
                        if ($i > 1) {
                            $start = clone $startNew;
                            $end = clone $endNew;
                        }
                        // Verify if 'end of month' is smaller than 'end of range'
                        $interval = $end->diff($endR);
                        // if diff is negative days
                        // then 'end of range' is before 'end of month'
                        if ($interval->days > 0 and $interval->invert == 1) {
                            $end = clone $endR;
                        }
                        if ($interval->days == 0) {
                            $end = clone $endR;
                        }
                        // if diff is positive days
                        // then 'end of range' is after 'end of month'
                        if ($interval->days > 0 and $interval->invert == 0) {

                        }
                        $currentBreakdown = $this->appExtension->formatLocalizedDate($end, 'Y');
                        $currentRange = new DateRange($start, $end);

                        $startNew = (clone $end)->modify('+1 day');
                        $endNew = (clone $startNew)->modify('last day of December this year');
                        break;
                }
                $sales[] = [
                    'date' => $currentBreakdown,
                    'orderCount' => $rep->countLast([
                        'period'=> 'dateRange',
                        'dateRange' => $this->dateRangeHelper->toDateRangeString($currentRange)
                    ]),
                    'totalRevenue' => $rep->sumLast([
                        'period'=> 'dateRange',
                        'dateRange' => $this->dateRangeHelper->toDateRangeString($currentRange)
                    ]),
                ];
            }
        }

        $pagerfanta = new Pagerfanta(new ArrayAdapter($sales));
//        $pagerfanta->setMaxPerPage($settings->get('general.itemsPerPage'));
        $pagerfanta->setMaxPerPage(50);

        try {
            $pagerfanta->setCurrentPage($page);
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $sales = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $sales[] = $result;
        }

        return $this->render('admin/analytics/sales-over-time.html.twig', [
            'currentPeriod' => $currentPeriod,
            'filterForm' => $filterForm->createView(),
            'filterTags' => $filterTags,
            'filterUrls' => $filterUrls,
            'sales' => $sales,
            'paginator' => $pagerfanta,
            'total' => $pagerfanta->getNbResults(),
        ]);
    }

    /**
     * @Route("/analytics/filter", name="analytics-filter")
     */
    public function handleFilterForm(Request $request)
    {
        $dataFromRequest = $request->request->all();
        $formName = array_keys($dataFromRequest)[0];

        // Since there are two Filter forms on the page with randomly created unique names (blockPrefixes -> see OrderFilterType)
        // the form name must be extracted from the Request data and used to recreate the very same form.
        $form = $this->get('form.factory')->createNamed($formName, AnalyticsFilterType::class);
//        $form = $this->createForm(OrderFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $dateRange = null;
            $groupBy = null;

            if ($filters['dateRange']) {
                $dateRange = $filters['dateRange'];
            }
            if ($filters['groupBy']) {
                $groupBy = $filters['groupBy'];
            }

            return $this->redirectToRoute('analytics-sales-over-time',[
                'dateRange' => $dateRange,
                'groupBy' => $groupBy,
            ]);
        }
        return $this->redirectToRoute('analytics-sales-over-time');
    }

    public function getGroupByString(string $groupBy)
    {
        $groupByString = null;
        switch ($groupBy) {
            case AnalyticsBreakdown::DAY:
                $groupByString = $this->translator->trans('analytics.day');
                break;
            case AnalyticsBreakdown::WEEK:
                $groupByString = $this->translator->trans('analytics.week');
                break;
            case AnalyticsBreakdown::MONTH:
                $groupByString = $this->translator->trans('analytics.month');
                break;
            case AnalyticsBreakdown::YEAR:
                $groupByString = $this->translator->trans('analytics.year');
                break;
            case AnalyticsBreakdown::NONE:
                $groupByString = $this->translator->trans('analytics.none');
                break;
        }
        return $groupByString;
    }

    public function getNumberOfBreakdowns(string $groupBy = null, DateRange $range = null)
    {
        $numberOfBreakdowns = 0;
        if ($range) {
            switch ($groupBy) {
                case AnalyticsBreakdown::DAY:
                    $numberOfBreakdowns = $this->dateRangeHelper->diff($range)->days + 1;
                    break;
                case AnalyticsBreakdown::WEEK:
                    $interval = $this->dateRangeHelper->diff($range);
                    if ($interval->days > 0 and $interval->invert == 1) {
                        $numberOfBreakdowns = $interval->days / 7;
                        $numberOfBreakdowns = (int) ceil($numberOfBreakdowns);
                    }
                    if ($interval->days == 0 and $interval->invert == 1) {
                        $numberOfBreakdowns = 1;
                    }

//                    dump($this->dateRangeHelper->diff($range));
//                    dump($numberOfBreakdowns);
//
//                    dd($numberOfBreakdowns);

//                    $dateTime = new \DateTime('2022-01-03');
//                    dump($dateTime->format('l'));
////                    $monday = (clone $dateTime)->modify(('Sunday' == $dateTime->format('l')) ? 'Monday last week' : 'Monday this week');
//                    $monday = (clone $dateTime)->modify('Monday this week');
//                    $sunday = (clone $dateTime)->modify('Sunday this week');
//
//                    dump($monday->format($this->defaultDateFormat));
//                    dump($sunday->format($this->defaultDateFormat));
//                    dd($dateTime->format($this->defaultDateFormat));
//
                    break;
                case AnalyticsBreakdown::MONTH:
                    $interval = $this->dateRangeHelper->diff($range);
//                    dump($interval);
                    $numberOfBreakdowns = 0;
                    if ($interval->y > 0) {
                        $numberOfBreakdowns = $interval->y * 12;
                    }
                    if ($interval->m >= 0) {
                        $startMonth = (int) $range->getStart()->format('n');
                        $endMonth = (int) $range->getEnd()->format('n');
                        $diff = $endMonth - $startMonth;
                        if ($diff < 0) {
                            $numberOfBreakdowns += $diff+1;
                            $numberOfBreakdowns += 12;
                        } else {
                            $numberOfBreakdowns += $diff+1;
                        }
                    }
//                    dd($numberOfBreakdowns);
                    break;
                case AnalyticsBreakdown::YEAR:
                    $startYear = (int) $range->getStart()->format('Y');
                    $endYear = (int) $range->getEnd()->format('Y');
                    $diff = $endYear-$startYear;
                    $numberOfBreakdowns = $diff+1;
                    break;
            }
        }
        return $numberOfBreakdowns;
    }
}