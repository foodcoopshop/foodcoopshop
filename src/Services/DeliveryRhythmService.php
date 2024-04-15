<?php
declare(strict_types=1);

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
namespace App\Services;

use Cake\Core\Configure;
use Cake\I18n\I18n;

class DeliveryRhythmService
{

    private $Time = null;

    public function __construct()
    {
        $this->Time = Configure::read('app.timeHelper');
    }

    public function getSendOrderListsWeekday()
    {
        $sendOrderListsWeekday = Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY') - Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA');
        if ($sendOrderListsWeekday < 0) {
            $sendOrderListsWeekday += 7;
        }
        return $sendOrderListsWeekday;
    }

    public function getDeliveryDateByCurrentDayForDb()
    {
        $deliveryDate = $this->getDeliveryDayByCurrentDay();
        $deliveryDate = date($this->Time->getI18Format('DatabaseAlt'), $deliveryDate);
        return $deliveryDate;
    }

    public function getNextWeeklyDeliveryDays($maxDays=52)
    {
        $nextDeliveryDay = $this->getDeliveryDateByCurrentDayForDb();
        return $this->Time->getWeekdayFormattedDaysList($nextDeliveryDay, $maxDays, 7);
    }

    public function getNextDailyDeliveryDays($maxDays)
    {
        $nextDeliveryDay = $this->Time->getTomorrowForDatabase();
        return $this->Time->getWeekdayFormattedDaysList($nextDeliveryDay, $maxDays, 1);
    }

    public function getDeliveryDayByCurrentDay()
    {
        return $this->getDeliveryDay($this->Time->getCurrentDay());
    }

    public function getDeliveryWeekday()
    {
        return Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY');
    }

    public function getLastOrderDay($nextDeliveryDay, $deliveryRhythmType, $deliveryRhythmSendOrderListWeekday, $deliveryRhythmOrderPossibleUntil)
    {

        if ($nextDeliveryDay == 'delivery-rhythm-triggered-delivery-break') {
            return '';
        }

        if ($deliveryRhythmType == 'individual') {
            $result = strtotime($deliveryRhythmOrderPossibleUntil->i18nFormat(Configure::read('DateFormat.Database')));
        } else {
            $lastOrderWeekday = $this->Time->getNthWeekdayBeforeWeekday(1, $deliveryRhythmSendOrderListWeekday);
            $tmpLocale = I18n::getLocale();
            I18n::setLocale('en_US');
            $weekdayAsNameInEnglish = $this->Time->getWeekdayName($lastOrderWeekday);
            I18n::setLocale($tmpLocale);
            $result = strtotime('last ' . $weekdayAsNameInEnglish, strtotime($nextDeliveryDay));
        }

        $result = date(Configure::read('DateFormat.DatabaseAlt'), $result);
        return $result;

    }

    public function getDbFormattedPickupDayByDbFormattedDate($date, $sendOrderListsWeekday = null, $deliveryRhythmType = null, $deliveryRhythmCount = null)
    {
        if (is_null($sendOrderListsWeekday)) {
            $sendOrderListsWeekday = $this->getSendOrderListsWeekday();
        }
        $pickupDay = $this->getDeliveryDay(strtotime($date), $sendOrderListsWeekday, $deliveryRhythmType, $deliveryRhythmCount);
        $pickupDay = date(Configure::read('DateFormat.DatabaseAlt'), $pickupDay);
        return $pickupDay;
    }

    public function getFormattedNextDeliveryDay($day)
    {
        return date($this->Time->getI18Format('DateShortAlt'), strtotime($this->getNextDeliveryDay($day)));
    }

    public function getOrderPeriodFirstDayByDeliveryDay(int $deliveryDay)
    {
        if ($this->hasSaturdayThursdayConfig()) {
            $deliveryDay = strtotime('-7 days', $deliveryDay);
        }
        return $this->getOrderPeriodFirstDay($deliveryDay);
    }

    public function getOrderPeriodLastDayByDeliveryDay(int $deliveryDay)
    {
        if ($this->hasSaturdayThursdayConfig()) {
            $deliveryDay = strtotime('-7 days', $deliveryDay);
        }
        return $this->getOrderPeriodLastDay($deliveryDay);
    }

    public function getNextDeliveryDay($day)
    {
        $orderPeriodFirstDay = $this->getOrderPeriodFirstDay($day);
        $deliveryDay = date($this->Time->getI18Format('DatabaseAlt'), $this->getDeliveryDay(strtotime($orderPeriodFirstDay)));
        if ($this->hasSaturdayThursdayConfig()) {
            $deliveryDay = date(
                $this->Time->getI18Format('DatabaseAlt'),
                strtotime($deliveryDay . '-7 days'),
            );
        }
        return $deliveryDay;
    }

    public function getDeliveryDay($orderDay, $sendOrderListsWeekday = null, $deliveryRhythmType = null, $deliveryRhythmCount = null)
    {
        if (is_null($deliveryRhythmType)) {
            $deliveryRhythmType = 'week';
        }
        if (is_null($deliveryRhythmCount)) {
            $deliveryRhythmCount = 1;
        }

        if (is_null($sendOrderListsWeekday)) {
            $sendOrderListsWeekday = $this->getSendOrderListsWeekday();
        }

        $daysToAddToOrderPeriodLastDay = $this->getDaysToAddToOrderPeriodLastDay();
        $deliveryDate = strtotime($this->getOrderPeriodLastDay($orderDay) . '+' . $daysToAddToOrderPeriodLastDay . ' days');

        $weekdayOrderDay = $this->Time->formatAsWeekday($orderDay);
        $weekdayOrderDay = $weekdayOrderDay % 7;

        $weekdayDeliveryDate = $this->Time->formatAsWeekday($deliveryDate);
        $weekdayStringDeliveryDate = strtolower(date('l', $deliveryDate));

        if ($this->hasSaturdayThursdayConfig()) {
            $calculateNextDeliveryDay = $weekdayOrderDay == 6 || (
                $weekdayOrderDay == 5 && $sendOrderListsWeekday == 5
            );
        } else {
            $calculateNextDeliveryDay = $weekdayOrderDay >= $sendOrderListsWeekday
                && $weekdayOrderDay <= $weekdayDeliveryDate;
        }

        if ($calculateNextDeliveryDay && $deliveryRhythmType != 'individual') {
            $preparedOrderDay = date($this->Time->getI18Format('DateShortAlt'), $orderDay);
            $deliveryDate = strtotime($preparedOrderDay . '+ ' . $deliveryRhythmCount .  ' ' . $deliveryRhythmType . ' ' . $weekdayStringDeliveryDate);
        }

        return $deliveryDate;
    }

    public function getOrderPeriodFirstDay($day)
    {

        $currentWeekday = $this->Time->formatAsWeekday($day);
        $dateDiff = 7 - $this->getSendOrderListsWeekday() + $currentWeekday;
        $date = strtotime('-' . $dateDiff . ' day ', $day);

        if ($this->hasSaturdayThursdayConfig()) {
            $addOneWeekCondition = in_array($currentWeekday, [6,7]) && $this->getDeliveryWeekday();
        } else {
            $addOneWeekCondition = $currentWeekday > $this->getDeliveryWeekday();
        }

        if ($addOneWeekCondition) {
            $date = strtotime('+7 day', $date);
        }

        $date = date($this->Time->getI18Format('DateShortAlt'), $date);

        return $date;
    }

    public function getOrderPeriodLastDay($day)
    {

        $currentWeekday = $this->Time->formatAsWeekday($day);

        if ($currentWeekday == 7) {
            $currentWeekday = 0;
        }

        $dateDiff = 0;
        $delta = Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA');
        $negatedDelta = $delta * -1;
        $deliveryWeekday = $this->getDeliveryWeekday();

        if ($currentWeekday == $deliveryWeekday) {
            $dateDiff = -1 - $delta;
        }
        if ($currentWeekday == ($deliveryWeekday + 1) % 7) {
            $dateDiff = $negatedDelta + 5;
        }
        if ($currentWeekday == ($deliveryWeekday + 2) % 7) {
            $dateDiff = $negatedDelta + 4;
        }
        if ($currentWeekday == ($deliveryWeekday + 3) % 7) {
            $dateDiff = $negatedDelta + 3;
        }
        if ($currentWeekday == ($deliveryWeekday + 4) % 7) {
            $dateDiff = $negatedDelta + 2;
        }
        if ($currentWeekday == ($deliveryWeekday + 5) % 7) {
            $dateDiff = $negatedDelta + 1;
        }
        if ($currentWeekday == ($deliveryWeekday + 6) % 7) {
            $dateDiff = $negatedDelta;
        }

        if ($this->hasSaturdayThursdayConfig() && $dateDiff < 0) {
            $dateDiff += 7;
        }

        $date = date($this->Time->getI18Format('DateShortAlt'), strtotime($dateDiff . ' day ', $day));

        return $date;
    }

    public function getNextDeliveryDayForProduct($product, $orderCustomerService)
    {
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $nextDeliveryDay = '1970-01-01';
        } elseif ($orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()) {
            $nextDeliveryDay = $this->Time->getCurrentDateForDatabase();
        } else {
            $nextDeliveryDay = $this->getNextPickupDayForProduct($product);
        }
        return $nextDeliveryDay;
    }

    public function getNextPickupDayForProduct($product, $currentDay=null)
    {

        if (is_null($currentDay)) {
            $currentDay = $this->Time->getCurrentDateForDatabase();
        }

        $sendOrderListsWeekday = null;
        if (!is_null($product->delivery_rhythm_send_order_list_weekday)) {
            $sendOrderListsWeekday = $product->delivery_rhythm_send_order_list_weekday;
        }

        $pickupDay = $this->getDbFormattedPickupDayByDbFormattedDate($currentDay, $sendOrderListsWeekday);

        if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
            return $pickupDay;
        }

        if (Configure::read('appDb.FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY')) {
            if ($product->delivery_rhythm_type == 'week' && $product->delivery_rhythm_count == 1) {
                $regularPickupDay = $this->getDbFormattedPickupDayByDbFormattedDate($currentDay);
                if ($pickupDay != $regularPickupDay) {
                    return 'delivery-rhythm-triggered-delivery-break';
                }
            }
        }

        if ($product->delivery_rhythm_type == 'week') {
            if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                $calculatedPickupDay = $product->delivery_rhythm_first_delivery_day->i18nFormat($this->Time->getI18Format('Database'));
                while($calculatedPickupDay < $pickupDay) {
                    $calculatedPickupDay = strtotime($calculatedPickupDay . '+' . $product->delivery_rhythm_count . ' week');
                    $calculatedPickupDay = date($this->Time->getI18Format('DatabaseAlt'), $calculatedPickupDay);
                }

                if (Configure::read('appDb.FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY')) {
                    if (in_array($product->delivery_rhythm_count, [1, 2]) && $pickupDay != $calculatedPickupDay) {
                        return 'delivery-rhythm-triggered-delivery-break';
                    }
                }

                $pickupDay = $calculatedPickupDay;
            }
        }

        if ($product->delivery_rhythm_type == 'month') {
            $ordinal = match($product->delivery_rhythm_count) {
                1 => 'first',
                2 => 'second',
                3 => 'third',
                4 => 'fourth',
                0 => 'last',
            };
            $deliveryDayAsWeekdayInEnglish = strtolower(date('l', strtotime($pickupDay)));
            $calculatedPickupDay = date($this->Time->getI18Format('DatabaseAlt'), strtotime($currentDay . ' ' . $ordinal . ' ' . $deliveryDayAsWeekdayInEnglish . ' of this month'));

            if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                $calculatedPickupDay = $product->delivery_rhythm_first_delivery_day->i18nFormat($this->Time->getI18Format('Database'));
            }

            while($calculatedPickupDay < $pickupDay) {
                $calculatedPickupDay = date($this->Time->getI18Format('DatabaseAlt'), strtotime($calculatedPickupDay . ' ' . $ordinal . ' ' . $deliveryDayAsWeekdayInEnglish . ' of next month'));
            }
            $pickupDay = $calculatedPickupDay;
        }

        if ($product->delivery_rhythm_type == 'individual') {
            $pickupDay = $product->delivery_rhythm_first_delivery_day->i18nFormat($this->Time->getI18Format('Database'));
        }

        return $pickupDay;

    }

    public function getDaysToAddToOrderPeriodLastDay()
    {
        return Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') + 1;
    }

    public function hasSaturdayThursdayConfig()
    {
        return $this->compareConfig(4, 5);
    }

    private static function compareConfig($weeklyPickupDay, $defaultSendOrderListsDayDelta)
    {
        return Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY') == $weeklyPickupDay &&
            Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') == $defaultSendOrderListsDayDelta;
    }

}
