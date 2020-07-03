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

    echo $this->Form->control('Carts.pickup_day', [
        'type' => 'select',
        'label' => __('Pickup_day').' <span class="after small"></span>',
        'options' => Configure::read('app.timeHelper')->getNextDeliveryDays(),
        'empty' => __('Please_select...'),
        'escape' => false,
    ]);

echo '</div>';

?>