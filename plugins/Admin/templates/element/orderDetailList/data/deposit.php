<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if ($groupBy == 'customer' && Configure::read('app.isDepositEnabled') && $this->Html->paymentIsCashless()) {
    echo '<td style="text-align:center;">';
    if (!$identity->isCustomer() || Configure::read('app.isCustomerAllowedToModifyOwnOrders')) {
        echo $this->element('addDepositPaymentOverlay', [
            'buttonText' => (!$isMobile ? __d('admin', 'Deposit_return') : ''),
            'objectId' => $orderDetail['customer_id'],
            'userName' => $orderDetail['name'],
            'customerId' => $orderDetail['customer_id'],
            'manufacturerId' => null,
        ]);
    } else {
        if ($orderDetail['sum_deposit'] > 0) {
            echo $this->Number->formatAsCurrency($orderDetail['sum_deposit']);
        }
    }
    echo '</td>';
}

if ($groupBy != 'customer' && Configure::read('app.isDepositEnabled')) {
    echo '<td class="right">';
    if ($groupBy == '') {
        if ($orderDetail->deposit > 0) {
            echo $this->Number->formatAsCurrency($orderDetail->deposit);
        }
    } else {
        if ($orderDetail['sum_deposit'] > 0) {
            echo $this->Number->formatAsCurrency($orderDetail['sum_deposit']);
        }
    }
    echo '</td>';
}

?>