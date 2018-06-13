<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper\TimeHelper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class MyTimeHelper extends TimeHelper
{
    
    public function getLastDayOfGivenMonth($monthAndYear)
    {
        return date('t', strtotime($monthAndYear));
    }

    public function getYearFromDbDate($dbDate)
    {
        return date('Y', strtotime($dbDate));
    }

    public function getCurrentDateForDatabase()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * should be called only once per request!
     * only implemented for requests on getWeekdaysBetweenOrderSendAndDelivery days
     * the rest of the days the date is nonsense!
     */
    public function recalcDeliveryDayDelta()
    {
        switch (date('N')) {
            case Configure::read('app.sendOrderListsWeekday'): // today is app.sendOrderListsWeekday
                $newDeliveryDelta = Configure::read('app.deliveryDayDelta');
                break;
            case Configure::read('app.sendOrderListsWeekday') + 1:
                $newDeliveryDelta = Configure::read('app.deliveryDayDelta') - 1;
                break;
            case Configure::read('app.sendOrderListsWeekday') + 2:
                $newDeliveryDelta = Configure::read('app.deliveryDayDelta') - 2;
                break;
            case Configure::read('app.sendOrderListsWeekday') + 3:
                $newDeliveryDelta = Configure::read('app.deliveryDayDelta') - 3;
                break;
        }
        if (isset($newDeliveryDelta)) {
            Configure::write('app.deliveryDayDelta', $newDeliveryDelta);
        }
    }

    public function getFormattedDeliveryDateByCurrentDay()
    {
        $deliveryDate = self::getDeliveryDayByCurrentDay();
        $deliveryDate = $this->getWeekdayName($this->formatAsWeekday($deliveryDate)) . ', ' . date('j.', $deliveryDate) . ' ' . $this->getMonthName(date('n', $deliveryDate)) . ' ' . date('Y', $deliveryDate);
        return $deliveryDate;
    }

    public function getDeliveryDayByCurrentDay()
    {

        $deliveryDate = self::getDeliveryDay($this->getCurrentDay());

        $weekdayDeliveryDate = $this->formatAsWeekday($deliveryDate);
        $weekdayStringDeliveryDate = strtolower(date('l', $deliveryDate));
        $day = $this->formatAsWeekday(time());

        if ($day >= Configure::read('app.sendOrderListsWeekday') && $day <= $weekdayDeliveryDate) {
            $deliveryDate = strtotime('+ 1 week ' . $weekdayStringDeliveryDate);
        }

        return $deliveryDate;
    }


    public function getDeliveryDay($day)
    {
        $daysToAddToOrderPeriodLastDay = Configure::read('app.deliveryDayDelta') + 1;
        $deliveryDate = strtotime($this->getOrderPeriodLastDay($day) . '+' . $daysToAddToOrderPeriodLastDay . ' days');
        return $deliveryDate;
    }

    public function getWeekdaysBetweenOrderSendAndDelivery($delta = 0)
    {
        $weekdays = [];
        for ($i = Configure::read('app.sendOrderListsWeekday'); $i <= Configure::read('app.sendOrderListsWeekday') + Configure::read('app.deliveryDayDelta') + $delta; $i++) {
            $weekdays[] = $i;
        }
        return $weekdays;
    }

    public function getCurrentWeekday()
    {
        return $this->formatAsWeekday($this->getCurrentDay());
    }

    public function formatAsWeekday($day)
    {
        return date('N', $day);
    }

    /**
     * @param day
     */
    public function getDateForShopOrder($day)
    {
        $currentWeekday = $this->formatAsWeekday($day);
        $daysDiff = $currentWeekday - Configure::read('app.sendOrderListsWeekday');
        $daysDiff = ($daysDiff * -1) - 1;
        $resetDate = strtotime($daysDiff . ' day', $day);
        return date('Y-m-d', $resetDate) . ' 00:00:00';
    }

    public function getCurrentDay()
    {
        return time();
    }

    private function getDeliveryWeekday()
    {
        return (Configure::read('app.sendOrderListsWeekday') + Configure::read('app.deliveryDayDelta')) % 7;
    }

    /**
     * see tests for implementations
     * @param $day
     * @return $day
     */
    public function getOrderPeriodFirstDay($day)
    {

        $currentWeekday = $this->formatAsWeekday($day);
        $dateDiff = 7 - Configure::read('app.sendOrderListsWeekday') + $currentWeekday;
        $date = strtotime('-' . $dateDiff . ' day ', $day);

        if ($currentWeekday > $this->getDeliveryWeekday()) {
            $date = strtotime('+7 day', $date);
        }

        $date = date('d.m.Y', $date);

        return $date;
    }

    /**
     * implemented for app.sendOrderListsWeekday == monday OR tuesday OR wednesday
     * @param $day
     * @return $day
     */
    public function getOrderPeriodLastDay($day)
    {

        $currentWeekday = $this->formatAsWeekday($day);

        if ($currentWeekday == 7) {
            $currentWeekday = 0;
        }

        if ($currentWeekday == $this->getDeliveryWeekday()) {
            $dateDiff = -1 - Configure::read('app.deliveryDayDelta');
        }
        if ($currentWeekday == ($this->getDeliveryWeekday() + 1) % 7) {
            $dateDiff = (Configure::read('app.deliveryDayDelta') * -1) + 5;
        }
        if ($currentWeekday == ($this->getDeliveryWeekday() + 2) % 7) {
            $dateDiff = (Configure::read('app.deliveryDayDelta') * -1) + 4;
        }
        if ($currentWeekday == ($this->getDeliveryWeekday() + 3) % 7) {
            $dateDiff = (Configure::read('app.deliveryDayDelta') * -1) + 3;
        }
        if ($currentWeekday == ($this->getDeliveryWeekday() + 4) % 7) {
            $dateDiff = (Configure::read('app.deliveryDayDelta') * -1) + 2;
        }
        if ($currentWeekday == ($this->getDeliveryWeekday() + 5) % 7) {
            $dateDiff = (Configure::read('app.deliveryDayDelta') * -1) + 1;
        }
        if ($currentWeekday == ($this->getDeliveryWeekday() + 6) % 7) {
            $dateDiff = Configure::read('app.deliveryDayDelta') * -1;
        }

        $date = date('d.m.Y', strtotime($dateDiff . ' day ', $day));

        return $date;
    }

    public function getWeekdays()
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

    public function getMonths()
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

    public function getAllMonthsForYear($year)
    {
        $months = $this->getMonths();
        $monthsForYear = [];
        foreach ($months as $key => $value) {
            $monthsForYear[$year.'-'.$key] = $value . ' ' . $year;
        }
        return $monthsForYear;
    }

    public function getWeekdayName($weekday)
    {
        $weekday = $weekday % 7;
        $weekdays = $this->getWeekdays();
        return $weekdays[$weekday];
    }

    public function getMonthName($month)
    {
        $months = $this->getMonths();
        return $months[$month];
    }

    public function getLastNDays($n, $startDate)
    {

        $startDate = strtotime($startDate);

        $days = [];
        for ($i=1; $i<=$n; $i++) {
            $deltaString = '-' . $i . ' days';
            $weekDay = date('w', strtotime($deltaString, $startDate));
            $days[date('Y-m-d H:i:s', strtotime($deltaString, $startDate))] = $this->getWeekdayName($weekDay) . ', ' . date('d.m.Y', strtotime($deltaString, $startDate));
        }
        return $days;
    }

    public function getFirstDayOfThisYear()
    {
        return date('d.m.Y', strtotime('first day of january'));
    }

    public function getLastDayOfThisYear()
    {
        return date('d.m.Y', strtotime('last day of december'));
    }

    public function getFirstDayOfLastMonth()
    {
        return date('d.m.Y', strtotime('first day of previous month'));
    }

    public function getLastDayOfLastMonth()
    {
        return date('d.m.Y', strtotime('last day of previous month'));
    }

    public function getLastMonthNameAndYear()
    {
        $previousMonthModifier = strtotime('first day of previous month');
        $lastMonthAndYearString = $this->getMonthName(date('n', $previousMonthModifier)) . ' ' . date('Y', $previousMonthModifier);
        return $lastMonthAndYearString;
    }

    /**
     * considers windows and unix
     * @param date $date
     * @return boolean
     */
    public function isDatabaseDateNotSet($date)
    {
        return $date == '01.01.1970' || $date == '30.11.-0001' || $date == '0000-00-00' || $date == '1000-01-01' || $date == null;
    }

    public function prepareDbDateForDatepicker($date)
    {
        $preparedDate = $this->formatToDateShort($date);
        if ($this->isDatabaseDateNotSet($date)) {
            return '';
        } else {
            return $preparedDate;
        }
    }

    /**
     * formats a timestamp to a short german date (e.g. 22.04.2007)
     *
     * @param integer $timestamp
     * @return Date
     */
    public function formatToDateShort($dbString)
    {
        $timestamp = strtotime($dbString);
        if ($dbString == '') {
            return '';
        }
        return date("d.m.Y", $timestamp);
    }

     /**
      * @param date $dbString must be in format dd.mm.YYYY
      * @return date in format YYYY-mm-dd
      */
    public function formatToDbFormatDate($dbString)
    {
        $dbString = str_replace('.', '-', $dbString);
        // BEWARE: strtotime() accepts/returns negative values since PHP5.1 in 64 bit versions
        $timestamp = strtotime($dbString);
        return date("Y-m-d", $timestamp);
    }

    public function formatForSavingAsDate($date)
    {
        $dbString = $this->formatToDbFormatDate($date);
        // since MySQL 5.7 there are no negative or zero-dates accepted anymore
        if ($dbString == '1970-01-01'
            || strpos($dbString, '-') === 0
        ) {
            $dbString = null;
        }
        return $dbString;
    }

     /**
     * formats a timestamp to a long date (e.g. 1. April 2007)
     *
     * @param integer $timestamp
     * @return Date
     */
    public function formatToDateLong($dbString)
    {
        $timestamp = strtotime($dbString);
        return strftime("%e. %B %Y", $timestamp);
    }

     /**
   * returns the difference of two dates in seconds
   * http://stackoverflow.com/questions/676824/how-to-calculate-the-difference-between-two-dates-using-php
   * läuft auch auf php4
   * @param date $date1 eg. date from db
   * @param date $date2 eg. date from db
   * @return int $seconds
   */
    public function datediff($date1, $date2)
    {
        $diff = abs(strtotime($date2) - strtotime($date1));
        return $diff;
    }

    /**
   *  formats a timestamp to a long date (e.g. 15.04.07 15:04)
   *  @param integer $timestamp
   *  @return string
  */
    public function formatToDateNTimeShort($dbString)
    {
        $timestamp = strtotime($dbString);
        return strftime('%d.%m.%y %H:%M', $timestamp);
    }

  /**
   *  formats a timestamp to a long date (e.g. 15.04.2007 15:04)
   *  @param integer $timestamp
   *  @return string
  */
    public function formatToDateNTimeLong($dbString)
    {
        $timestamp = strtotime($dbString);
        return strftime('%d.%m.%Y %H:%M', $timestamp);
    }

  /**
   *  formats a timestamp to a long date (e.g. 15.04.2007 15:04:22)
   *  @param integer $timestamp
   *  @return string
  */
    public function formatToDateNTimeLongWithSecs($dbString)
    {
        $timestamp = strtotime($dbString);
        return strftime('%d.%m.%Y %H:%M:%S', $timestamp);
    }

   /**
   *
   * @param $timestampOrDateNTime must be in format dd.mm.YYYY hh:mm:ss or timestamp
   * @return datetime in format YYYY-mm-dd hh:mm:ss
   */
    public function formatToDbFormatDateNTime($timestampOrDateNTime)
    {

        $timestamp = $timestampOrDateNTime;

        if (preg_match('/ /', $timestampOrDateNTime)) { // parameter war kein timestamp
            $dbString = str_replace('.', '-', $timestampOrDateNTime);
            $timestamp = strtotime($dbString);
        }

        return date("Y-m-d H:i:s", $timestamp);
    }
}
