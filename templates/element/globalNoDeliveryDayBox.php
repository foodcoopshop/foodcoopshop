<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use App\Services\OrderCustomerService;

if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
    return;
}

$orderCustomerService = new OrderCustomerService();
if ($orderCustomerService->isOrderForDifferentCustomerMode() || $orderCustomerService->isSelfServiceModeByUrl()) {
    return;
}

$globalNoDeliveryDaysString = $this->Html->getGlobalNoDeliveryDaysString();
if ($globalNoDeliveryDaysString != '') {
    echo '<div id="global-no-delivery-day-box" class="box">';
        echo '<h3>' . __('Attention_delivery_break!') . '</h3>';
        echo '<p>' . $globalNoDeliveryDaysString . '</p>';
    echo '</div>';
}
