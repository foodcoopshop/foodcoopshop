<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
    return;
}

echo '<div class="select-pickup-day-wrapper">';

    $formattedAndCleanedDeliveryDays = $this->Html->getFormattedAndCleanedDeliveryDays(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'));
    $formattedToDatabaseDeliveryDays = [];
    foreach($formattedAndCleanedDeliveryDays as $f) {
        $formattedToDatabaseDeliveryDays[] = $this->Time->formatToDbFormatDate($f);
    }

    $preparedDeliveryDays = $this->Time->getNextDailyDeliveryDays(15);
    $i = 0;
    foreach($preparedDeliveryDays as $k => $v) {
        if (in_array($k, $formattedToDatabaseDeliveryDays)) {
            $preparedDeliveryDays[$k] = $v . ' (' . __('Delivery_break') . ')';
        }
        if ($i== 0) {
            unset($preparedDeliveryDays[$k]); // remove today
        }
        $i++;
    }

    echo $this->Form->control('Carts.pickup_day', [
        'type' => 'select',
        'label' => __('Pickup_day').' <span class="after small"></span>',
        'options' => $preparedDeliveryDays,
        'disabled' => $formattedToDatabaseDeliveryDays,
        'empty' => __('Please_select...'),
        'escape' => false,
    ]);

echo '</div>';

?>