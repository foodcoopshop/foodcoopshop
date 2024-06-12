<?php
declare(strict_types=1);

/**
 * - this file contains the specific configuration for your foodcoop
 * - configurations in config.php can be overriden in this file
 * - please rename it to "custom.config.php" to use it
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use App\Model\Entity\OrderDetail;

if (!defined('ACCESS_DENIED_MESSAGE')) {
    define('ACCESS_DENIED_MESSAGE', __('Access_denied_please_sign_in.'));
}

return [
    'app' => [
        'manufacturerComponensationInfoText' => __('This_order_contains_the_variable_member_fee.'),
        'orderStates' => [
            OrderDetail::STATE_OPEN => __('order_state_order_placed'),
            OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER => __('order_state_order_list_sent_to_manufacturer'),
            OrderDetail::STATE_BILLED_CASHLESS => __('order_state_billed_cashless'),
            OrderDetail::STATE_BILLED_CASH => __('order_state_billed_cash')
        ],
        'currencyName' => Configure::read('app.htmlHelper')->getCurrencyName(Configure::read('appDb.FCS_CURRENCY_SYMBOL')),
        'selfServiceLoginCustomers' => [
            '93',
            '94'
        ],
    ]
];
?>