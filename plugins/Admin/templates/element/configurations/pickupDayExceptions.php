<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Services\DeliveryRhythmService;
use App\Services\PickupDayExceptionService;
use Cake\Core\Configure;

$pickupDayExceptionService = new PickupDayExceptionService();
$rows = $pickupDayExceptionService->decode($configuration->getInvalidField('value') ?? $configuration->value);
$rows['__INDEX__'] = ['original_pickup_day' => '', 'pickup_day' => '', 'order_possible_until' => ''];
$dayOptions = $this->Time->getWeekdayFormattedDaysList(date('Y-m-d'), 380, 1);
$originalPickupDayOptions = [];
$globalNoDeliveryDays = $this->Html->getGlobalNoDeliveryDaysAsArray();
foreach((new DeliveryRhythmService())->getNextWeeklyDeliveryDays() as $day => $text) {
    $originalPickupDayOptions[$day] = $text . (in_array($day, $globalNoDeliveryDays) ? ' (' . __('Delivery_break') . ')' : '');
}
$selects = [
    'original_pickup_day' => $originalPickupDayOptions,
    'pickup_day' => $dayOptions,
    'order_possible_until' => $dayOptions,
];

$columnLabels = [
    'original_pickup_day' => __('Regular_pickup_day'),
    'pickup_day' => __('New_pickup_day'),
    'order_possible_until' => __('Order_possible_until') . ' (' . __('midnight') . ')',
];

// rows are added and removed dynamically
$this->Form->unlockField('Configurations.value');

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace') . '.Admin.initPickupDayExceptions(' . count($rows) . ', ' . json_encode(PickupDayExceptionService::PICKUP_DAY_OFFSETS) . ', ' . json_encode(PickupDayExceptionService::ORDER_POSSIBLE_UNTIL_OFFSETS) . ');'
]);

echo '<div class="input pickup-day-exceptions-wrapper">';
echo '<div class="pickup-day-exceptions-table-wrapper">';
echo '<label>' . $label . '</label>';
echo '<a href="javascript:void(0);" class="btn btn-outline-light add-pickup-day-exception-button"><i class="fas fa-plus-circle ok"></i> ' . __('Add_pickup_day_exception') . '</a>';
echo '<table id="pickup-day-exceptions">';
echo '<tr class="header' . (count($rows) == 1 ? ' hide' : '') . '"><th>' . implode('</th><th>', $columnLabels) . '</th><th></th></tr>';
foreach($rows as $index => $row) {
    echo '<tr class="exception-row' . ($index === '__INDEX__' ? ' template hide' : '') . '">';
    foreach($selects as $key => $options) {
        echo '<td data-label="' . h($columnLabels[$key]) . '">' . $this->Form->control('Configurations.value.' . $index . '.' . $key, [
            'type' => 'select',
            'label' => false,
            'id' => false,
            'class' => 'no-tom-select ' . $key,
            'empty' => __('Please_select...'),
            'options' => $options + ($row[$key] != '' ? [$row[$key] => $this->Time->getDateFormattedWithWeekday(strtotime($row[$key]))] : []),
            'value' => $row[$key],
            'disabled' => $key == 'original_pickup_day' ? $globalNoDeliveryDays : false,
        ]) . '</td>';
    }
    echo '<td><a href="javascript:void(0);" class="btn btn-outline-light delete-button" title="' . __('Delete') . '"><i class="far fa-trash-alt not-ok"></i></a></td>';
    echo '</tr>';
    $infos = [];
    if (!in_array('', $row, true)) {
        $changedOrderDeadlines = $pickupDayExceptionService->getProductsWithChangedOrderDeadline($row);
        if (!empty($changedOrderDeadlines)) {
            $info = '<h4><i class="far fa-clock"></i> ' . __('Individual_order_deadline') . '</h4>' . __('Products_with_own_order_deadline_for_regular_pickup_day_{0}_moved_forward:', ['<b>' . $this->Time->formatToDateShort($row['original_pickup_day']) . '</b>']) . '<ul>';
            foreach($changedOrderDeadlines as $changedOrderDeadline) {
                $info .= '<li>' . __('{0}:_{1}_products_order_deadline_{2}_moved_to_{3}', [
                    '<b>' . h($changedOrderDeadline['manufacturer_name']) . '</b>',
                    $changedOrderDeadline['product_count'],
                    $this->Time->getDateFormattedWithWeekday(strtotime($changedOrderDeadline['regular_last_order_day'])),
                    $this->Time->getDateFormattedWithWeekday(strtotime($changedOrderDeadline['new_last_order_day'])),
                ]) . '</li>';
            }
            $infos[] = '<div class="info-section">' . $info . '</ul></div>';
        }
    }
    $bulkOrders = $row['original_pickup_day'] != '' ? $pickupDayExceptionService->getBulkOrders($row['original_pickup_day']) : [];
    if (!empty($bulkOrders)) {
        $info = '<h4><i class="fas fa-boxes-stacked"></i> ' . __('Bulk_orders') . '</h4>' . __('Bulk_orders_with_regular_pickup_day_{0}_are_not_moved:', ['<b>' . $this->Time->formatToDateShort($row['original_pickup_day']) . '</b>']) . '<ul>';
        foreach($bulkOrders as $bulkOrder) {
            $info .= '<li>' . __('{0}:_{1}_bulk_order_products_with_{2}_ordered_products_and_order_value_{3}', [
                '<b>' . h($bulkOrder['manufacturer_name']) . '</b>',
                $bulkOrder['product_count'],
                $bulkOrder['ordered_amount'],
                $this->Number->formatAsCurrency($bulkOrder['order_value']),
            ]) . '</li>';
        }
        $infos[] = '<div class="info-section">' . $info . '</ul></div>';
    }
    if (!empty($infos)) {
        echo '<tr class="exception-info"><td colspan="4">' . implode('', $infos) . '</td></tr>';
    }
}
echo '</table>';
echo '</div>';
if ($configuration->getError('value')) {
    echo '<div class="error-message">' . implode('<br />', (array) $configuration->getError('value')) . '</div>';
}
echo '</div>';
