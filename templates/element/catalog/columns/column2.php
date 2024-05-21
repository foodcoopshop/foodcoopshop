<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Services\DeliveryRhythmService;
use Cake\Core\Configure;

echo '<div class="heading">';
    echo '<h4>';
    if ($showProductDetailLink) {
        echo '<a class="product-name" href="'.$this->Slug->getProductDetail($product->id_product, $product->name).'">'.$product->name.'</a>';
    } else {
        echo $product->name;
    }
    echo '</h4>';
echo '</div>';

echo '<div class="descriptions">';
    if ($product->description_short != '') {
        echo $product->description_short.'<br />';
    }

    if ($product->description != '') {
        echo $this->Html->link(
            '<i class="fa"></i> '.__('Show_more'),
            'javascript:void(0);',
            [
                'class' => 'toggle-link',
                'title' => __('More_infos_to_product_{0}', [h($product->name)]),
                'escape' => false
            ]
            );
        echo '<div class="toggle-content description">'.$product->description.'</div>';
    }
echo '</div>';

if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {

    if (!$orderCustomerService->isOrderForDifferentCustomerMode() && !($product->manufacturer->stock_management_enabled && $product->is_stock_product)) {

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
                echo '<span class="last-order-day">';
                echo '<br />' . __('Order_possible_until') . ': ' . $this->Time->getDateFormattedWithWeekday(strtotime($lastOrderDay));
                echo '</span>';
            }

    }

    if (!$orderCustomerService->isSelfServiceModeByUrl()) {
        echo '<br />'.__('Pickup_day').': ';
    }
    echo '<span class="pickup-day">';
    if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
        $pickupDayDetailText = __('Instant_order');
    } else {
        $pickupDayDetailText = $this->Html->getDeliveryRhythmString(
            $product->is_stock_product && $product->manufacturer->stock_management_enabled,
            $product->delivery_rhythm_type,
            $product->delivery_rhythm_count,
            );
    }
    if ($product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break') {
        echo $this->Time->getDateFormattedWithWeekday(strtotime($product->next_delivery_day));
    }
    echo '</span>';
    if (!$orderCustomerService->isSelfServiceModeByUrl()) {
        echo ' (' . $pickupDayDetailText . ')';
    }
    if (!$orderCustomerService->isSelfServiceModeByUrl() && !$orderCustomerService->isOrderForDifferentCustomerMode()) {
        if (
            $product->next_delivery_day != 'delivery-rhythm-triggered-delivery-break'
            && strtotime($product->next_delivery_day) != (new DeliveryRhythmService())->getDeliveryDayByCurrentDay()
            ) {
                $weeksAsFloat = (strtotime($product->next_delivery_day) - strtotime(date($this->MyTime->getI18Format('DateShortAlt')))) / 24/60/60;
                $fullWeeks = (int) ($weeksAsFloat / 7);
                $days = (int) $weeksAsFloat % 7;
                if ($days == 0) {
                    echo ' - <b>'. __('{0,plural,=1{1_week} other{#_weeks}}', [$fullWeeks]) . '</b>';
                } else {
                    echo ' - <b>'. __('{0,plural,=1{1_week} other{#_weeks}} {1,plural,=1{and_1_day} other{and_#_days}}', [$fullWeeks, $days]) . '</b>';
                }
            }
    }
}

if (Configure::read('app.showManufacturerListAndDetailPage')) {
    echo '<br />'.__('Manufacturer').': ';
    if ($showManufacturerDetailLink) {
        echo $this->Html->link(
            $product->manufacturer->name,
            $this->Slug->getManufacturerDetail($product->id_manufacturer, $product->manufacturer->name),
            [
                'escape' => false
            ]
            );
    } else {
        echo $product->manufacturer->name;
    }
}

if (!$orderCustomerService->isOrderForDifferentCustomerMode()) {
    if ($identity !== null) {
        if ($identity->isSuperadmin() || ($identity->isManufacturer() && $product->id_manufacturer == $identity->getManufacturerId())) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt"></i>',
                $this->Slug->getProductAdmin(($identity->isSuperadmin() ? $product->id_manufacturer : null), $product->id_product),
                [
                    'class' => 'btn btn-outline-light edit-shortcut-button',
                    'title' => __('Edit'),
                    'escape' => false
                ]
                );
        }
    }
}
