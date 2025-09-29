<?php

declare(strict_types=1);

use App\Services\OrderCustomerService;
use Cake\Core\Configure;
use App\Services\DeliveryRhythmService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
    return;
}

if (OrderCustomerService::isSelfServiceModeByUrl() || OrderCustomerService::isOrderForDifferentCustomerMode()) {
    return;
}

$infos = [];

$defaultIcon = 'far fa-clock fa-flip-horizontal ok';
$delayIcon = 'fas fa-clock-rotate-left fa-flip-horizontal not-ok';

if ($product->delivery_rhythm_type == 'individual') {
    $infos[] = '<b>' . __('bulk_order') . '</b>';
} else {
    $icon = $defaultIcon;
    $pickupDayDetailText = $this->Html->getDeliveryRhythmString(
        $product->is_stock_product && $product->manufacturer->stock_management_enabled,
        $product->delivery_rhythm_type,
        $product->delivery_rhythm_count,
    );
    $infos[] = '<b>' . __('Delivery_rhythm') . '</b>: ' . $pickupDayDetailText;
}

if (!($product->manufacturer->stock_management_enabled && $product->is_stock_product)) {

    $lastOrderDay = (new DeliveryRhythmService())->getLastOrderDay(
        $product->next_delivery_day,
        $product->delivery_rhythm_type,
        $product->delivery_rhythm_send_order_list_weekday,
        $product->delivery_rhythm_order_possible_until,
    );

    if (!($product->delivery_rhythm_type == 'week'
        && $product->delivery_rhythm_count == 1
        && (new DeliveryRhythmService())->getSendOrderListsWeekday() == $product->delivery_rhythm_send_order_list_weekday
        )
        && $lastOrderDay != ''
        ) {
            $infos[] = __('Order_possible_until') . ': ' . $this->Time->getDateFormattedWithWeekday(strtotime($lastOrderDay));
        }

}

$pickupDayInfo = '';
if ($product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break') {
    $pickupDayInfo = $this->Time->getDateFormattedWithWeekday(strtotime($product->next_delivery_day));
}

if ($product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break'
    && strtotime($product->next_delivery_day) != (new DeliveryRhythmService())->getDeliveryDayByCurrentDay()
    ) {
        $icon = $delayIcon;
        $weeksAsFloat = (strtotime($product->next_delivery_day) - strtotime(date($this->MyTime->getI18Format('DateShortAlt')))) / 24/60/60;
        $fullWeeks = (int) ($weeksAsFloat / 7);
        $days = (int) $weeksAsFloat % 7;
        if ($days == 0) {
            $pickupDayInfo .= ' (<b>'. __('{0,plural,=1{1_week} other{#_weeks}}', [$fullWeeks]) . '</b>)';
        } else {
            $pickupDayInfo .= ' (<b>'. __('{0,plural,=1{1_week} other{#_weeks}} {1,plural,=1{and_1_day} other{and_#_days}}', [$fullWeeks, $days]) . '</b>)';
        }
    }

$infos[] = $pickupDayInfo;

echo '<div class="fcs-badge" title="' . h(implode('<br />', array_values($infos))) . '">';
    echo '<img src="/img/badge-ring-light.svg" />';
    echo '<i class="' . $icon . ' fa-fw"></i>';
echo '</div>';

