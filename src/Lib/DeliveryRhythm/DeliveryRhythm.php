<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\DeliveryRhythm;

use Cake\Core\Configure;
use Cake\I18n\I18n;

class DeliveryRhythm
{

    public static function getSendOrderListsWeekday()
    {
        $sendOrderListsWeekday = Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY') - Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA');
        if ($sendOrderListsWeekday < 0) {
            $sendOrderListsWeekday += 7;
        }
        return $sendOrderListsWeekday;
    }

    public static function getDeliveryDateByCurrentDayForDb()
    {
        $deliveryDate = self::getDeliveryDayByCurrentDay();
        $deliveryDate = date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), $deliveryDate);
        return $deliveryDate;
    }

    public static function getNextWeeklyDeliveryDays($maxDays=52)
    {
        $nextDeliveryDay = self::getDeliveryDateByCurrentDayForDb();
        return Configure::read('app.timeHelper')->getWeekdayFormatedDaysList($nextDeliveryDay, $maxDays, 7);
    }

    public static function getNextDailyDeliveryDays($maxDays)
    {
        $nextDeliveryDay = Configure::read('app.timeHelper')->getTomorrowForDatabase();
        return Configure::read('app.timeHelper')->getWeekdayFormatedDaysList($nextDeliveryDay, $maxDays, 1);
    }

    public static function getDeliveryDayByCurrentDay()
    {
        return self::getDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
    }

    public static function getDeliveryWeekday()
    {
        return Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY');
    }

    public static function getLastOrderDay($nextDeliveryDay, $deliveryRhythmType, $deliveryRhythmCount, $deliveryRhythmSendOrderListWeekday, $deliveryRhythmOrderPossibleUntil)
    {

        if ($nextDeliveryDay == 'delivery-rhythm-triggered-delivery-break') {
            return '';
        }

        if ($deliveryRhythmType == 'individual') {
            $result = strtotime($deliveryRhythmOrderPossibleUntil->i18nFormat(Configure::read('DateFormat.Database')));
        } else {
            $lastOrderWeekday = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, $deliveryRhythmSendOrderListWeekday);
            $tmpLocale = I18n::getLocale();
            I18n::setLocale('en_US');
            $weekdayAsNameInEnglish = Configure::read('app.timeHelper')->getWeekdayName($lastOrderWeekday);
            I18n::setLocale($tmpLocale);
            $result = strtotime('last ' . $weekdayAsNameInEnglish, strtotime($nextDeliveryDay));
        }

        $result = date(Configure::read('DateFormat.DatabaseAlt'), $result);
        return $result;

    }

    public static function getDbFormattedPickupDayByDbFormattedDate($date, $sendOrderListsWeekday = null, $deliveryRhythmType = null, $deliveryRhythmCount = null)
    {
        if (is_null($sendOrderListsWeekday)) {
            $sendOrderListsWeekday = self::getSendOrderListsWeekday();
        }
        $pickupDay = self::getDeliveryDay(strtotime($date), $sendOrderListsWeekday, $deliveryRhythmType, $deliveryRhythmCount);
        $pickupDay = date(Configure::read('DateFormat.DatabaseAlt'), $pickupDay);
        return $pickupDay;
    }

    public static function getFormattedNextDeliveryDay($day)
    {
        return date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(self::getNextDeliveryDay($day)));
    }

    public static function getNextDeliveryDay($day)
    {
        $orderPeriodFirstDay = self::getOrderPeriodFirstDay($day);
        return date(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), self::getDeliveryDay(strtotime($orderPeriodFirstDay)));
    }

    public static function getDeliveryDayForSendOrderListsCronjob($day)
    {
        $deliveryDay = self::getNextDeliveryDay($day);
        if (self::hasSaturdayThursdayConfig()) {
            $deliveryDay = date(
                Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'),
                strtotime($deliveryDay . '-7 days'),
            );
        }
        return $deliveryDay;
    }

    public static function getDeliveryDay($orderDay, $sendOrderListsWeekday = null, $deliveryRhythmType = null, $deliveryRhythmCount = null)
    {
        if (is_null($deliveryRhythmType)) {
            $deliveryRhythmType = 'week';
        }
        if (is_null($deliveryRhythmCount)) {
            $deliveryRhythmCount = 1;
        }

        if (is_null($sendOrderListsWeekday)) {
            $sendOrderListsWeekday = self::getSendOrderListsWeekday();
        }

        $daysToAddToOrderPeriodLastDay = self::getDaysToAddToOrderPeriodLastDay();
        $deliveryDate = strtotime(self::getOrderPeriodLastDay($orderDay) . '+' . $daysToAddToOrderPeriodLastDay . ' days');

        $weekdayOrderDay = Configure::read('app.timeHelper')->formatAsWeekday($orderDay);
        $weekdayOrderDay = $weekdayOrderDay % 7;

        $weekdayDeliveryDate = Configure::read('app.timeHelper')->formatAsWeekday($deliveryDate);
        $weekdayStringDeliveryDate = strtolower(date('l', $deliveryDate));

        if (self::hasSaturdayThursdayConfig()) {
            $calculateNextDeliveryDay = $weekdayOrderDay == 6 || (
                $weekdayOrderDay == 5 && $sendOrderListsWeekday == 5
            );
        } else {
            $calculateNextDeliveryDay = $weekdayOrderDay >= $sendOrderListsWeekday
                && $weekdayOrderDay <= $weekdayDeliveryDate;
        }

        if ($calculateNextDeliveryDay && $deliveryRhythmType != 'individual') {
            $preparedOrderDay = date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), $orderDay);
            $deliveryDate = strtotime($preparedOrderDay . '+ ' . $deliveryRhythmCount .  ' ' . $deliveryRhythmType . ' ' . $weekdayStringDeliveryDate);
        }

        return $deliveryDate;
    }

    public static function getOrderPeriodFirstDay($day)
    {

        $currentWeekday = Configure::read('app.timeHelper')->formatAsWeekday($day);
        $dateDiff = 7 - self::getSendOrderListsWeekday() + $currentWeekday;
        $date = strtotime('-' . $dateDiff . ' day ', $day);

        if (self::hasSaturdayThursdayConfig()) {
            $addOneWeekCondition = in_array($currentWeekday, [6,7]) && self::getDeliveryWeekday();
        } else {
            $addOneWeekCondition = $currentWeekday > self::getDeliveryWeekday();
        }

        if ($addOneWeekCondition) {
            $date = strtotime('+7 day', $date);
        }

        $date = date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), $date);

        return $date;
    }

    public static function getOrderPeriodLastDay($day)
    {

        $currentWeekday = Configure::read('app.timeHelper')->formatAsWeekday($day);

        if ($currentWeekday == 7) {
            $currentWeekday = 0;
        }

        if ($currentWeekday == self::getDeliveryWeekday()) {
            $dateDiff = -1 - Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA');
        }
        if ($currentWeekday == (self::getDeliveryWeekday() + 1) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 5;
        }
        if ($currentWeekday == (self::getDeliveryWeekday() + 2) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 4;
        }
        if ($currentWeekday == (self::getDeliveryWeekday() + 3) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 3;
        }
        if ($currentWeekday == (self::getDeliveryWeekday() + 4) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 2;
        }
        if ($currentWeekday == (self::getDeliveryWeekday() + 5) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 1;
        }
        if ($currentWeekday == (self::getDeliveryWeekday() + 6) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1);
        }

        if (self::hasSaturdayThursdayConfig() && $dateDiff < 0) {
            $dateDiff += 7;
        }

        $date = date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime($dateDiff . ' day ', $day));


        return $date;
    }

    public static function getDaysToAddToOrderPeriodLastDay()
    {
        return Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') + 1;
    }

    public static function hasSaturdayThursdayConfig()
    {
        return self::compareConfig(4, 5);
    }

    private static function compareConfig($weeklyPickupDay, $defaultSendOrderListsDayDelta)
    {
        return Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY') == $weeklyPickupDay &&
            Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') == $defaultSendOrderListsDayDelta;
    }

}
