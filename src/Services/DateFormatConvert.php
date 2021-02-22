<?php

namespace App\Services;

/**
 * Utility service:
 *      - converts date format:
 *      - PHP format >>> MomentJs format
 *
 * Class DateFormatConvert
 * @package App\Services
 */
final class DateFormatConvert
{
    // Function from here: https://stackoverflow.com/a/55173613
    public function convertDateFormatFromPhpToMomentJs(string $phpFormat): string
    {
        $replacements = [
            'A' => 'A',      // for the sake of escaping below
            'a' => 'a',      // for the sake of escaping below
            'B' => '',       // Swatch internet time (.beats), no equivalent
            'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
            'D' => 'ddd',
            'd' => 'DD',
            'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
            'F' => 'MMMM',
            'G' => 'H',
            'g' => 'h',
            'H' => 'HH',
            'h' => 'hh',
            'I' => '',       // Daylight Saving Time? => moment().isDST();
            'i' => 'mm',
            'j' => 'D',
            'L' => '',       // Leap year? => moment().isLeapYear();
            'l' => 'dddd',
            'M' => 'MMM',
            'm' => 'MM',
            'N' => 'E',
            'n' => 'M',
            'O' => 'ZZ',
            'o' => 'YYYY',
            'P' => 'Z',
            'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
            'S' => 'o',
            's' => 'ss',
            'T' => 'z',      // deprecated since version 1.6.0 of moment.js
            't' => '',       // days in the month => moment().daysInMonth();
            'U' => 'X',
            'u' => 'SSSSSS', // microseconds
            'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
            'W' => 'W',      // for the sake of escaping below
            'w' => 'e',
            'Y' => 'YYYY',
            'y' => 'YY',
            'Z' => '',       // time zone offset in minutes => moment().zone();
            'z' => 'DDD',
        ];

        // Converts escaped characters.
        foreach ($replacements as $from => $to) {
            $replacements['\\' . $from] = '[' . $from . ']';
        }

        return strtr($phpFormat, $replacements);
    }
}