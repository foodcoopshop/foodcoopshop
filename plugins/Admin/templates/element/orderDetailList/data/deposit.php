<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if ($groupBy == 'customer' && Configure::read('app.isDepositEnabled') && Configure::read('app.isDepositPaymentCashless')) {
    echo '<td'.(!$isMobile ? ' style="width: 144px;"' : '').'>';
    if (!$appAuth->isCustomer() || Configure::read('app.isCustomerAllowedToModifyOwnOrders')) {
        echo $this->element('addDepositPaymentOverlay', [
            'buttonText' => (!$isMobile ? __d('admin', 'Deposit_return') : ''),
            'rowId' => $orderDetail['customer_id'],
            'userName' => $orderDetail['name'],
            'customerId' => $orderDetail['customer_id'],
            'manufacturerId' => null
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