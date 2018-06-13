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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

define('ACCESS_DENIED_MESSAGE', __('Access_denied_please_sign_in.'));

// called only for translation
__('order_state_cash');

return [
    'app' => [
        'manufacturerComponensationInfoText' => __('This_order_contains_the_variable_member_fee.'),
        'visibleOrderStates' => [
            ORDER_STATE_OPEN => __('order_state_open'),
            ORDER_STATE_CASH_FREE => __('order_state_closed'),
        ]
    ]
];
?>