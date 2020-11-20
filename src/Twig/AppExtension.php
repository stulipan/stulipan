<?php

namespace App\Twig;

//use ApiPlatform\Core\Tests\Fixtures\TestBundle\Entity\ThirdLevel;
use App\Services\FileUploader;
use App\Services\Localization;
use App\Services\StoreSettings;
use DateTime;
use Exception;
use phpDocumentor\Reflection\Types\This;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private const UA_BROWSER_NAME = 'name';
    private const UA_BROWSER_VERSION = 'version';
    private const UA_BROWSER_PLATFORM = 'platform';

    private $container;
    private $locale;
    private $translator;
    private $settings;

    public function __construct(ContainerInterface $container, SessionInterface $session,
                                Localization $localization, TranslatorInterface $translator, StoreSettings $settings)
    {
        $this->container = $container;
        $this->locale = $localization->getLocale($session->get('_locale', 'hu'));
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function getFunctions(): array
    {
        return [
          new TwigFunction('uploaded_asset', [$this, 'getPathOfUploadedAsset'])
        ];
    }

    public function getPathOfUploadedAsset(string $path): string
    {
        return $this->container
            ->get(FileUploader::class)
            ->getPublicPath($path);
    }

    public static function getSubscribedServices()
    {
        return [
            FileUploader::class,
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('browser', [$this, 'formatBrowserInfo']),
            new TwigFilter('timeAgo', [$this, 'formatTimeAgo']),
            new TwigFilter('localizedDate', [$this, 'formatLocalizedDate']),
            new TwigFilter('localizedTime', [$this, 'formatLocalizedTime']),
            new TwigFilter('money', [$this, 'formatMoney']),
            new TwigFilter('number', [$this, 'formatNumber']),
        ];
    }

    public function formatMoney($amount)
    {
        switch ($this->locale->getCode()) {
            case 'hu':
                return number_format($amount, 0, ',','.').' '.$this->locale->getCurrencySymbol();
                break;
            case 'en':
                return number_format($amount, 0, '.',',').' '.$this->locale->getCurrencySymbol();
                break;
        }
        return number_format($amount, 0, ',','.');
    }

    public function formatNumber($number)
    {
        switch ($this->locale->getCode()) {
            case 'hu':
                return number_format($number, 0, ',',' ');
                break;
            case 'en':
                return number_format($number, 0, '.',',');
                break;
        }
        return number_format($number, 0, ',','.');
    }

    public function formatTimeAgo($datetime, $level=1)
    {
//        For dates older than 2 days, date will not be displayed as timeAgo
//        if ($datetime->diff(new DateTime())->days > 2) {
//            return strtolower($this->formatLocalizedDate($datetime, 'Y M j. | H:i'));
//        }
        return $this->calculateTimeElapsed($datetime, $level);
    }

    /**
     * Takes a DateTime object and returns that value in 'X days ago' or 'X days and Y hours ago'
     *
     *
     * @param $datetime
     * @param int $level    Example:    $level=1            $level=3
     * @return string                   'X days ago'        'X months ago, Y days ago and Z hours ago'
     * @throws Exception
     */
    private function calculateTimeElapsed($datetime, $level=6)
    {
        $lang = [
            'second' => $this->translator->trans('datetime.timeAgo.second'),
            'seconds' => $this->translator->trans('datetime.timeAgo.seconds'),
            'minute' => $this->translator->trans('datetime.timeAgo.minute'),
            'minutes' => $this->translator->trans('datetime.timeAgo.minutes'),
            'hour' => $this->translator->trans('datetime.timeAgo.hour'),
            'hours' => $this->translator->trans('datetime.timeAgo.hours'),
            'day' => $this->translator->trans('datetime.timeAgo.day'),
            'days' => $this->translator->trans('datetime.timeAgo.days'),
            'month' => $this->translator->trans('datetime.timeAgo.month'),
            'months' => $this->translator->trans('datetime.timeAgo.months'),
            'year' => $this->translator->trans('datetime.timeAgo.year'),
            'years' => $this->translator->trans('datetime.timeAgo.years'),
            'and' => $this->translator->trans('datetime.timeAgo.and'),
            'prefix' => $this->translator->trans('datetime.timeAgo.prefix'),
            'postfix' => $this->translator->trans('datetime.timeAgo.postfix'),
        ];

        if (!$datetime instanceof DateTime) {
            throw new Exception('STUPID: A Twig >> AppExtension.php >> calculateTimeElapsed() fügvényben nem DateTime típusú a dátum!');
        }

        $date = $datetime;  // $datetime must be instanceof \DateTime();
        $date = $date->diff(new DateTime());

        // build array
        $since = array_combine(array('year', 'month', 'day', 'hour', 'minute', 'second'), explode(',', $date->format('%y,%m,%d,%h,%i,%s')));

        // remove empty date values
        $since = array_filter($since);

        // output only the first x date values
        $since = array_slice($since, 0, $level);

        // build string
        $last_key = key(array_slice($since, -1, 1, true));
        $string = '';
        foreach ($since as $key => $val) {
            // separator
            if ($string) {
                $string .= $key != $last_key ? ', ' : ' ' . $lang['and'] . ' ';
            }
            // set plural
            $key .= $val > 1 ? 's' : '';
            // add date value
            $string .= $val . ' ' . $lang[ $key ];
        }
        if (isset($lang['prefix']) && $lang['prefix'] !== '') {
            return $lang['prefix'].' '.$string;
        }
        if (isset($lang['postfix']) && $lang['postfix'] !== '') {
            return $string.' '.$lang['postfix'];
        }
        return $string;
    }

    public function formatLocalizedDate($dateTime, string $format=null) {

        if ($format == null || $format == '') {
            if ($this->settings->get('general.date-format')) {
                $format = $this->settings->get('general.date-format');
            } else {
                $format='Y-m-d';
            }
        }
        $shortMonths = [
            'jan' => $this->translator->trans('datetime.jan'),
            'feb' => $this->translator->trans('datetime.feb'),
            'mar' => $this->translator->trans('datetime.mar'),
            'apr' => $this->translator->trans('datetime.apr'),
            'may' => $this->translator->trans('datetime.may'),
            'jun' => $this->translator->trans('datetime.jun'),
            'jul' => $this->translator->trans('datetime.jul'),
            'aug' => $this->translator->trans('datetime.aug'),
            'sep' => $this->translator->trans('datetime.sep'),
            'oct' => $this->translator->trans('datetime.oct'),
            'nov' => $this->translator->trans('datetime.nov'),
            'dec' => $this->translator->trans('datetime.dec'),
        ];
        $longMonths = [
            'january' => $this->translator->trans('datetime.january'),
            'february' => $this->translator->trans('datetime.february'),
            'march' => $this->translator->trans('datetime.march'),
            'april' => $this->translator->trans('datetime.april'),
            'mayy' => $this->translator->trans('datetime.mayy'),
            'june' => $this->translator->trans('datetime.june'),
            'july' => $this->translator->trans('datetime.july'),
            'august' => $this->translator->trans('datetime.august'),
            'september' => $this->translator->trans('datetime.september'),
            'october' => $this->translator->trans('datetime.october'),
            'november' => $this->translator->trans('datetime.november'),
            'december' => $this->translator->trans('datetime.december'),
        ];
        $shortWeekdays = [
            'mon' => $this->translator->trans('datetime.mon'),
            'tue' => $this->translator->trans('datetime.tue'),
            'wed' => $this->translator->trans('datetime.wed'),
            'thu' => $this->translator->trans('datetime.thu'),
            'fri' => $this->translator->trans('datetime.fri'),
            'sat' => $this->translator->trans('datetime.sat'),
            'sun' => $this->translator->trans('datetime.sun'),
        ];
        $longWeekdays = [
            'monday' => $this->translator->trans('datetime.monday'),
            'tuesday' => $this->translator->trans('datetime.tuesday'),
            'wednesday' => $this->translator->trans('datetime.wednesday'),
            'thursday' => $this->translator->trans('datetime.thursday'),
            'friday' => $this->translator->trans('datetime.friday'),
            'saturday' => $this->translator->trans('datetime.saturday'),
            'sunday' => $this->translator->trans('datetime.sunday'),
        ];

        $dateTime = $dateTime->format($format);  // eg: Feb 13, Thu    OR    2019-10-19
        $localizedDateTime = '';
        foreach ($longMonths as $month => $localizedMonth) {
            if (stristr($dateTime,$month)) {
                $dateTime = str_ireplace($month, ucfirst($localizedMonth), $dateTime);
            }
        }
        foreach ($shortMonths as $month => $localizedMonth) {
            if (stristr($dateTime,$month)) {
                $dateTime = str_ireplace($month, ucfirst($localizedMonth), $dateTime);
            }
        }
        foreach ($longWeekdays as $weekday => $localizedWeekday) {
            if (stristr($dateTime,$weekday)) {
                $dateTime = str_ireplace($weekday, ucfirst($localizedWeekday), $dateTime);
            }
        }
        foreach ($shortWeekdays as $weekday => $localizedWeekday) {
            if (stristr($dateTime,$weekday)) {
                $dateTime = str_ireplace($weekday, ucfirst($localizedWeekday), $dateTime);
            }
        }

//        if ($localizedDateTime === '') {
            return $dateTime;
//        }
//        return $localizedDateTime;

    }

    public function formatLocalizedTime($dateTime, string $format=null)
    {
        if ($format == null || $format == '') {
            if ($this->settings->get('general.time-format')) {
                $format = $this->settings->get('general.time-format');
            } else {
                // Default format
                $format='H:i';
            }
        }
        $dateTime = $dateTime->format($format);  // eg: 19:20, 19:20:34    OR    07:20am
        return $dateTime;
    }

    public function formatBrowserInfo(string $userAgent, string $filter)
    {
        if ($filter === AppExtension::UA_BROWSER_NAME) {
            $browser = $this->getBrowser($userAgent);
            return $browser['name'];
        }
        if ($filter === AppExtension::UA_BROWSER_VERSION) {
            $browser = $this->getBrowser($userAgent);
            return $browser['version'];
        }
        if ($filter === AppExtension::UA_BROWSER_PLATFORM) {
            $browser = $this->getBrowser($userAgent);
            return $browser['platform'];
        }
    }

    /**
     * A function which converts user-agent to readable information: browser name, version and platform
     * Eg: Google Chrome, 77.0.3865.120, mac
     *
     * From here: https://www.php.net/manual/en/function.get-browser.php#101125
     *
     * @param string $userAgent
     * @return array
     */
    private function getBrowser(string $userAgent)
    {
        $u_agent = $userAgent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }
    
//    // This is for converting object to array   >>> NOT IN USE
//    public function getFilters()
//    {
//        return array(
//            new \Twig_SimpleFilter('cast_to_array', array($this, 'objectToArrayFilter')),
//        );
//    }
//
//    public function objectToArrayFilter($stdClassObject) {
//        // Just typecast it to an array
//        $response = (array)$stdClassObject;
//
//        return $response;
//    }
    
}