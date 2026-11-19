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
use App\View\Helper\MyTimeHelper;
use App\Model\Entity\Product;
use Cake\I18n\Date;

class DeliveryRhythmService
{

    private MyTimeHelper $Time;
 
    public function __construct()
    {
        $this->Time = Configure::read('app.timeHelper');
    }

    public function getSendOrderListsWeekday(): int
    {
        $sendOrderListsWeekday = Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY') - Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA');
        if ($sendOrderListsWeekday < 0) {
            $sendOrderListsWeekday += 7;
        }
        return $sendOrderListsWeekday;
    }

    public function getDeliveryDateByCurrentDayForDb(): string
    {
        $deliveryDate = date($this->Time->getI18Format('DatabaseAlt'), $this->getDeliveryDay($this->Time->getCurrentDay()));
        return $deliveryDate;
    }

    /**
     * @return array<string, string>
     */
    public function getNextWeeklyDeliveryDays(int $maxDays=52): array
    {
        $nextDeliveryDay = $this->getDeliveryDateByCurrentDayForDb();
        return $this->Time->getWeekdayFormattedDaysList($nextDeliveryDay, $maxDays, 7);
    }

    /**
     * @return array<string, string>
     */
    public function getNextDailyDeliveryDays(int $maxDays): array
    {
        $nextDeliveryDay = $this->Time->getTomorrowForDatabase();
        return $this->Time->getWeekdayFormattedDaysList($nextDeliveryDay, $maxDays, 1);
    }

    public function getDeliveryDayByCurrentDay(): int
    {
        return $this->getDeliveryDayWithPickupDayExceptions($this->Time->getCurrentDay());
    }

    public function getDeliveryDayWithPickupDayExceptions(int $orderDay): int
    {
        $regularPickupDay = date('Y-m-d', $this->getDeliveryDay($orderDay));
        return strtotime($this->applyPickupDayException($regularPickupDay, date('Y-m-d', $orderDay), $this->getSendOrderListsWeekday(),
            fn($nextOrderDay) => date('Y-m-d', $this->getDeliveryDayWithPickupDayExceptions(strtotime($nextOrderDay))),
        ));
    }

    /**
     * the earlier order deadline (product or exception) is used
     * if the order deadline is over, the order is assigned to the following pickup day
     * @param callable(string): string $getPickupDayForNextOrderPeriod
     */
    private function applyPickupDayException(string $regularPickupDay, string $orderDay, int $sendOrderListsWeekday, callable $getPickupDayForNextOrderPeriod): string
    {
        $exception = (new PickupDayExceptionService())->getByOriginalPickupDay($regularPickupDay);
        if (is_null($exception)) {
            return $regularPickupDay;
        }
        $orderDay = date('Y-m-d', strtotime($orderDay));
        $regularLastOrderDay = $this->getLastOrderDay($regularPickupDay, 'week', $sendOrderListsWeekday, null);
        if ($orderDay <= min($regularLastOrderDay, $exception['order_possible_until'])) {
            return $exception['pickup_day'];
        }
        return $getPickupDayForNextOrderPeriod((new PickupDayExceptionService())->addDays(max($orderDay, $regularLastOrderDay), 1));
    }

    /**
     * @return array<string, list<int>> pickup day => send order list weekdays of the products whose order lists are sent at the given day
     */
    public function getPickupDaysForSendingOrderLists(string $cronjobRunDay): array
    {
        $pickupDayExceptionService = new PickupDayExceptionService();
        $runDay = date('Y-m-d', strtotime($cronjobRunDay));
        $result = [];

        $regularPickupDay = $this->getNextDeliveryDay(strtotime($runDay));
        $exception = $pickupDayExceptionService->getByOriginalPickupDay($regularPickupDay);
        if (is_null($exception)) {
            $result[$regularPickupDay] = [(int) date('w', strtotime($runDay))];
        } elseif ($runDay <= $exception['order_possible_until']) {
            // products with an earlier order deadline keep their send day
            $result[$exception['pickup_day']] = [(int) date('w', strtotime($runDay))];
        }

        foreach($pickupDayExceptionService->getExceptions() as $exception) {
            if ($pickupDayExceptionService->addDays($exception['order_possible_until'], 1) == $runDay) {
                // all products whose regular send day is not before the send day of the exception
                for ($day = $runDay; $day < $exception['original_pickup_day']; $day = $pickupDayExceptionService->addDays($day, 1)) {
                    $result[$exception['pickup_day']][] = (int) date('w', strtotime($day));
                }
            }
        }
        return $result;
    }

    public function getDeliveryWeekday(): int
    {
        return (int) Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY');
    }

    public function getLastOrderDay(string $nextDeliveryDay, string $deliveryRhythmType, int $deliveryRhythmSendOrderListWeekday, ?Date $deliveryRhythmOrderPossibleUntil): string
    {

        if ($nextDeliveryDay == 'delivery-rhythm-triggered-delivery-break') {
            return '';
        }

        $exception = (new PickupDayExceptionService())->getByPickupDay($nextDeliveryDay);
        if ($deliveryRhythmType != 'individual' && !is_null($exception)) {
            return min($this->getLastOrderDay($exception['original_pickup_day'], $deliveryRhythmType, $deliveryRhythmSendOrderListWeekday, null), $exception['order_possible_until']);
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

    public function getDbFormattedPickupDayByDbFormattedDate(string $date, ?int $sendOrderListsWeekday = null, ?string $deliveryRhythmType = null, ?int $deliveryRhythmCount = null): string
    {
        if (is_null($sendOrderListsWeekday)) {
            $sendOrderListsWeekday = $this->getSendOrderListsWeekday();
        }
        $pickupDay = $this->getDeliveryDay(strtotime($date), $sendOrderListsWeekday, $deliveryRhythmType, $deliveryRhythmCount);
        $pickupDay = date(Configure::read('DateFormat.DatabaseAlt'), $pickupDay);
        return $pickupDay;
    }

    public function getFormattedNextDeliveryDay(int $day): string
    {
        if (!$this->hasSaturdayToWednesdayOrThursdayConfig()) {
            $day = strtotime('-2 days', $day);
        }
        return date($this->Time->getI18Format('DateShortAlt'), strtotime($this->getNextDeliveryDay($day)));
    }

    public function getOrderPeriodFirstDayByDeliveryDay(int $deliveryDay): string
    {
        if ($this->hasSaturdayToWednesdayOrThursdayConfig()) {
            $deliveryDay = strtotime('-7 days', $deliveryDay);
        }
        return $this->getOrderPeriodFirstDay($deliveryDay);
    }

    public function getOrderPeriodLastDayByDeliveryDay(int $deliveryDay): string
    {
        if ($this->hasSaturdayToWednesdayOrThursdayConfig()) {
            $deliveryDay = strtotime('-7 days', $deliveryDay);
        }
        return $this->getOrderPeriodLastDay($deliveryDay);
    }

    public function getNextDeliveryDay(int $day): string
    {
        $orderPeriodFirstDay = $this->getOrderPeriodFirstDay($day);
        $deliveryDay = date($this->Time->getI18Format('DatabaseAlt'), $this->getDeliveryDay(strtotime($orderPeriodFirstDay)));
        if ($this->hasSaturdayToWednesdayOrThursdayConfig()) {
            $deliveryDay = date(
                $this->Time->getI18Format('DatabaseAlt'),
                strtotime($deliveryDay . '-7 days'),
            );
        }
        return $deliveryDay;
    }

    public function getDeliveryDay(int $orderDay, ?int $sendOrderListsWeekday = null, ?string $deliveryRhythmType = null, ?int $deliveryRhythmCount = null): int
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

        if ($this->hasSaturdayToWednesdayOrThursdayConfig()) {
            $calculateNextDeliveryDay = $weekdayOrderDay == 6 || ($weekdayOrderDay == 5 && $sendOrderListsWeekday == 5);
        } else {
            $calculateNextDeliveryDay = $weekdayOrderDay >= $sendOrderListsWeekday && $weekdayOrderDay <= $weekdayDeliveryDate;
        }

        if ($calculateNextDeliveryDay && $deliveryRhythmType != 'individual') {
            $preparedOrderDay = date($this->Time->getI18Format('DateShortAlt'), $orderDay);
            $deliveryDate = strtotime($preparedOrderDay . '+ ' . $deliveryRhythmCount .  ' ' . $deliveryRhythmType . ' ' . $weekdayStringDeliveryDate);
        }

        return $deliveryDate;
    }

    public function getOrderPeriodFirstDay(int $day): string
    {

        $currentWeekday = $this->Time->formatAsWeekday($day);
        $dateDiff = 7 - $this->getSendOrderListsWeekday() + $currentWeekday;
        $date = strtotime('-' . $dateDiff . ' day ', $day);

        if ($this->hasSaturdayToWednesdayOrThursdayConfig()) {
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

    public function getOrderPeriodLastDay(int $day): string
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

        if ($this->hasSaturdayToWednesdayOrThursdayConfig() && $dateDiff < 0) {
            $dateDiff += 7;
        }

        $date = date($this->Time->getI18Format('DateShortAlt'), strtotime($dateDiff . ' day ', $day));

        return $date;
    }

    public function getNextDeliveryDayForProduct(Product $product): string
    {
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $nextDeliveryDay = '1970-01-01';
        } elseif (OrderCustomerService::isOrderForDifferentCustomerMode() || OrderCustomerService::isSelfServiceModeByUrl()) {
            $nextDeliveryDay = $this->Time->getCurrentDateForDatabase();
        } else {
            $nextDeliveryDay = $this->getNextPickupDayForProduct($product);
        }
        return $nextDeliveryDay;
    }

    public function getNextPickupDayForProduct(Product $product, ?string $currentDay=null): string
    {

        if (is_null($currentDay)) {
            $currentDay = $this->Time->getCurrentDateForDatabase();
        }

        $pickupDay = $this->getRegularNextPickupDayForProduct($product, $currentDay);
        if ($pickupDay == 'delivery-rhythm-triggered-delivery-break' || $product->delivery_rhythm_type == 'individual') {
            return $pickupDay;
        }
        return $this->applyPickupDayException($pickupDay, $currentDay, $product->delivery_rhythm_send_order_list_weekday ?? $this->getSendOrderListsWeekday(),
            fn($nextOrderDay) => $this->getNextPickupDayForProduct($product, $nextOrderDay),
        );
    }

    private function getRegularNextPickupDayForProduct(Product $product, string $currentDay): string
    {

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
                default => '',
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

    public function getDaysToAddToOrderPeriodLastDay(): int
    {
        return Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') + 1;
    }

    public function hasSaturdayToWednesdayOrThursdayConfig(): bool
    {
        return $this->hasSaturdayWednesdayConfig() || $this->hasSaturdayThursdayConfig();
    }

    public function hasSaturdayWednesdayConfig(): bool
    {
        return $this->compareConfig(3, 4);
    }

    public function hasSaturdayThursdayConfig(): bool
    {
        return $this->compareConfig(4, 5);
    }


    private static function compareConfig(int $weeklyPickupDay, int $defaultSendOrderListsDayDelta): bool
    {
        return Configure::read('appDb.FCS_WEEKLY_PICKUP_DAY') == $weeklyPickupDay &&
            Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') == $defaultSendOrderListsDayDelta;
    }

}
