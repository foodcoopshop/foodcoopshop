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

class DeliveryRhythm
{

    public static function getOrderPeriodLastDay($day)
    {

        $currentWeekday = Configure::read('app.timeHelper')->formatAsWeekday($day);

        if ($currentWeekday == 7) {
            $currentWeekday = 0;
        }

        if ($currentWeekday == Configure::read('app.timeHelper')->getDeliveryWeekday()) {
            $dateDiff = -1 - Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA');
        }
        if ($currentWeekday == (Configure::read('app.timeHelper')->getDeliveryWeekday() + 1) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 5;
        }
        if ($currentWeekday == (Configure::read('app.timeHelper')->getDeliveryWeekday() + 2) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 4;
        }
        if ($currentWeekday == (Configure::read('app.timeHelper')->getDeliveryWeekday() + 3) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 3;
        }
        if ($currentWeekday == (Configure::read('app.timeHelper')->getDeliveryWeekday() + 4) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 2;
        }
        if ($currentWeekday == (Configure::read('app.timeHelper')->getDeliveryWeekday() + 5) % 7) {
            $dateDiff = (Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1) + 1;
        }
        if ($currentWeekday == (Configure::read('app.timeHelper')->getDeliveryWeekday() + 6) % 7) {
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
        if (self::hasSaturdayThursdayConfig()) {
            return 5;
        } else {
            return Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') + 1;
        }
    }

    public static function hasTuesdayFridayConfig()
    {
        return self::compareConfig(5, 3);
    }

    public static function hasWednesdayFridayConfig()
    {
        return self::compareConfig(5, 2);
    }

    public static function hasThursdayFridayConfig()
    {
        return self::compareConfig(5, 1);
    }

    public static function hasMondayTuesdayConfig()
    {
        return self::compareConfig(2, 1);
    }

    public static function hasMondayThursdayConfig()
    {
        return self::compareConfig(4, 3);
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
