<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper\TimeHelper;
use Cake\I18n\DateTime;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class MyTimeHelper extends TimeHelper
{

    public function getTranslatedTimeInterval($timeInterval): string
    {
        return match($timeInterval) {
            'day' => __('daily'),
            'week' => __('weekly'),
            'month' => __('monthly'),
            default => '',
        };
    }

    public function convertSecondsInMinutesAndSeconds($seconds): string
    {
        $secondsAsInteger = (int) $seconds;
        $decimals = round($seconds, 2) - $secondsAsInteger;
        $minutesAsDecimal = $secondsAsInteger / 60;
        $minutesAsModulo = (int) $minutesAsDecimal % 60;
        $minutes = floor($minutesAsModulo);
        $seconds = $secondsAsInteger % 60;
        $result = [];
        if ($minutes > 0) {
            $result[] = __('{0,plural,=1{1_minute} other{#_minutes}}', [$minutes]);
        }
        $result[] = __('{0,plural,=1{1_second} other{#_seconds}}', [$seconds + $decimals]);
        return join(' ', $result);
    }

    public function getAllYearsUntilThisYear(int $thisYear, int $firstYear, $labelPrefix=''): array
    {
        $years = [];
        while($thisYear >= $firstYear) {
            $years[$thisYear] = $labelPrefix . $thisYear;
            $thisYear--;
        }
        return $years;
    }

    public function getAllMonthsUntilThisYear($thisYear, $firstYear): array
    {
        $monthsAndYear = $this->getAllMonthsForYear($thisYear);
        while($thisYear >= $firstYear) {
            $monthsAndYear = array_merge($this->getAllMonthsForYear($thisYear), $monthsAndYear);
            $thisYear--;
        }
        return $monthsAndYear;
    }

    public function sortArrayByDate(array $array): array
    {
        usort($array, function($a, $b) {
            return strtotime($a) - strtotime($b);
        });
        return $array;
    }

    public function getTimeObjectUTC($time): DateTime
    {
        return DateTime::createFromTimestamp(strtotime($time), 'UTC');
    }

    public function getTimezoneDiffInSeconds($timestamp): string
    {
        return date('Z', $timestamp);
    }

    public function getI18Format(string $formatString): string
    {
        return Configure::read('DateFormat.' . $formatString);
    }

    public function getLastDayOfGivenMonth($monthAndYear): string
    {
        return date('t', strtotime($monthAndYear));
    }

    public function getYearFromDbDate($dbDate): string
    {
        return date('Y', strtotime($dbDate));
    }

    public function getCurrentDateTimeForDatabase(): string
    {
        return date($this->getI18Format('DatabaseWithTimeAlt'));
    }
    public function getCurrentDateTimeForFilename(): string
    {
        return date($this->getI18Format('DateWithTimeForFilename'));
    }

    public function getCurrentDateForDatabase(): string
    {
        return date($this->getI18Format('DatabaseAlt'));
    }

    public function getNthWeekdayBeforeWeekday(int $n, int $weekday): int
    {
        $beforeWeekday = $weekday - $n;
        if ($beforeWeekday < 0) {
            $beforeWeekday += 7;
        }
        return $beforeWeekday;
    }

    public function getNthWeekdayAfterWeekday(int $n, int $weekday): int
    {
        $beforeWeekday = $weekday + $n;
        if ((int) $beforeWeekday > 6) {
            $beforeWeekday -= 7;
        }
        return $beforeWeekday;
    }

    public function getDateFormattedWithWeekday($date): string
    {
        $date = $this->getWeekdayName($this->formatAsWeekday($date)) . ', ' . date($this->getI18Format('DateShortAlt'), $date);
        return $date;
    }

    public function getTomorrowForDatabase(): string
    {
        return $this->getInXDaysForDatabase(1);
    }

    public function getInXDaysForDatabase($days): string
    {
        return date(Configure::read('DateFormat.DatabaseAlt'), strtotime($this->getCurrentDateForDatabase() . ' +' . $days . ' days'));
    }

    public function getWeekdayFormattedDaysList($day, $maxDays, $factor): array
    {
        $days = [
            $day => $this->getDateFormattedWithWeekday(strtotime($day))
        ];
        $count = 1;
        while($count < $maxDays) {
            $nextCalculatedDay = date(Configure::read('DateFormat.DatabaseAlt'), strtotime($day . ' + ' . $count * $factor . ' day'));
            $days[$nextCalculatedDay] = $this->getDateFormattedWithWeekday(strtotime($nextCalculatedDay));
            $count++;
        }
        return $days;
    }

    /**
     * In ISO-8601 specification, it says that December 28th is always in the last week of its year.
     * https://stackoverflow.com/questions/3319386/php-get-last-week-number-in-year
     */
    public function getLastCalendarWeekOfYear($year): string
    {
        return date('W', strtotime($year . '-12-28'));
    }

    public function getAllCalendarWeeksUntilNow($timestampStart): array
    {

        $startCalendarWeek = date('W', $timestampStart);
        $startYear = (int) date('Y', $timestampStart);
        $currentYear = (int) date('Y');
        $allYears = array_reverse($this->getAllYearsUntilThisYear($currentYear, $startYear));
        $currentCalendarWeek = date('W');

        $result = [];
        foreach($allYears as $year)
        {
            $result = match($year) {
                $startYear => array_merge($result, $this->getCalendarWeeks($startCalendarWeek, $this->getLastCalendarWeekOfYear($startYear), $year)),
                $currentYear => array_merge($result, $this->getCalendarWeeks(1, $currentCalendarWeek, $year)),
                default => array_merge($result, $this->getCalendarWeeks(1, $this->getLastCalendarWeekOfYear($year), $year)),
            };
        }

        return $result;
    }

    public function getCalendarWeeks($firstWeek, $lastWeek, $year): array
    {
        $firstWeek = (int) $firstWeek;
        $lastWeek = (int) $lastWeek;

        $result = [];
        while($firstWeek <= $lastWeek) {
            $firstWeekAsString = $firstWeek;
            if ($firstWeek < 10) {
                $firstWeekAsString = '0' . $firstWeek;
            }
            $result[] = $year. '-' . $firstWeekAsString;
            $firstWeek++;
        }
        return $result;
    }

    public function getCurrentWeekday(): int
    {
        return $this->formatAsWeekday($this->getCurrentDay());
    }

    public function formatAsWeekday($day): int
    {
        return (int) date('N', $day);
    }

    public function getCurrentDay(): int
    {
        return time();
    }

    public function getWeekdays(): array
    {
        $weekdays = [
          0 => __('Sunday'),
          1 => __('Monday'),
          2 => __('Tuesday'),
          3 => __('Wednesday'),
          4 => __('Thursday'),
          5 => __('Friday'),
          6 => __('Saturday')
        ];
        return $weekdays;
    }

    public function getMonths(): array
    {
        $months = [
            1 =>  __('January'),
            2 =>  __('February'),
            3 =>  __('March'),
            4 =>  __('April'),
            5 =>  __('May'),
            6 =>  __('June'),
            7 =>  __('July'),
            8 =>  __('August'),
            9 =>  __('September'),
            10 => __('October'),
            11 => __('November'),
            12 => __('December')
        ];
        return $months;
    }

    public function getAllMonthsForYear($year): array
    {
        $months = $this->getMonths();
        $monthsForYear = [];
        foreach ($months as $key => $value) {
            $monthsForYear[$year.'-'.$key] = $value . ' ' . $year;
        }
        return $monthsForYear;
    }

    public function getWeekdayName($weekday): string
    {
        $weekday = $weekday % 7;
        $weekdays = $this->getWeekdays();
        return $weekdays[$weekday];
    }

    public function getMonthName($month): string
    {
        $months = $this->getMonths();
        return $months[$month];
    }

    public function getLastNDays($n, $startDate): array
    {

        $startDate = strtotime($startDate);

        $days = [];
        for ($i=1; $i<=$n; $i++) {
            $deltaString = '-' . $i . ' days';
            $weekDay = date('w', strtotime($deltaString, $startDate));
            $days[date(Configure::read('DateFormat.DatabaseWithTimeAlt'), strtotime($deltaString, $startDate))] = $this->getWeekdayName($weekDay) . ', ' . date($this->getI18Format('DateShortAlt'), strtotime($deltaString, $startDate));
        }
        return $days;
    }

    public function getFirstDayOfThisYear(): string
    {
        return date($this->getI18Format('DateShortAlt'), strtotime('first day of january'));
    }

    public function getLastDayOfThisYear(): string
    {
        return date($this->getI18Format('DateShortAlt'), strtotime('last day of december'));
    }

    public function getLastMonthNameAndYear(): string
    {
        $previousMonthModifier = strtotime('first day of previous month');
        $lastMonthAndYearString = $this->getMonthName(date('n', $previousMonthModifier)) . ' ' . date('Y', $previousMonthModifier);
        return $lastMonthAndYearString;
    }

    public function getFirstDayOfThisMonth(): string
    {
        return date($this->getI18Format('DateShortAlt'), strtotime('first day of this month'));
    }

    public function getLastDayOfThisMonth(): string
    {
        return date($this->getI18Format('DateShortAlt'), strtotime('last day of this month'));
    }

    public function getFirstDayOfLastMonth($date): string
    {
        return date($this->getI18Format('DateShortAlt'), strtotime($date . ' first day of previous month'));
    }

    public function getLastDayOfLastMonth($date): string
    {
        return date($this->getI18Format('DateShortAlt'), strtotime($date . ' last day of previous month'));
    }

    public function getNumberOfDays($timestamp): string
    {
        return date('t', $timestamp);
    }

    public function isDatabaseDateNotSet($date): bool
    {
        return $date == '1970-01-01' || $date == '01.01.1970' || $date == '30.11.-0001' || $date == '0000-00-00' || $date == '1000-01-01' || $date == null;
    }

    public function prepareDbDateForDatepicker($date): string
    {
        $preparedDate = $this->formatToDateShort($date);
        if ($this->isDatabaseDateNotSet($date)) {
            return '';
        } else {
            return $preparedDate;
        }
    }

    public function formatToDateShort($dbString): string
    {
        $timestamp = strtotime($dbString);
        if ($dbString == '') {
            return '';
        }
        return date($this->getI18Format('DateShortAlt'), $timestamp);
    }

    public function formatToDbFormatDate($dateString): string
    {
        $timestamp = strtotime($dateString);
        $result = date(Configure::read('DateFormat.DatabaseAlt'), (int) $timestamp);
        return $result;
    }

}
