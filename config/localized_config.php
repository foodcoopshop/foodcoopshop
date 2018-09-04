<?php
/**
 * - this file contains the specific configuration for your foodcoop
 * - configurations in config.php can be overriden in this file
 * - please rename it to "custom.config.php" to use it
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

define('ACCESS_DENIED_MESSAGE', __('Access_denied_please_sign_in.'));

// called only for translation
__('order_state_cash');

return [
    'app' => [
        'manufacturerComponensationInfoText' => __('This_order_contains_the_variable_member_fee.'),
        'orderStates' => [
            ORDER_STATE_ORDER_PLACED => __('order_state_order_placed'),
            ORDER_STATE_CASH_FREE => __('order_state_closed'),
            ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER => __('order_state_order_list_sent_to_manufacturer'),
            ORDER_STATE_BILLED_CASHLESS => __('order_state_billed_cashless'),
            ORDER_STATE_BILLED_CASH => __('order_state_billed_cash')
        ],
        'currencyName' => Configure::read('app.htmlHelper')->getCurrencyName(Configure::read('appDb.FCS_CURRENCY_SYMBOL'))
    ]
];
?>