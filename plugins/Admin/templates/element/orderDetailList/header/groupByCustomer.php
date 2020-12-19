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

echo '<th>';
echo $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Member'));
echo '</th>';

echo '<th>';
echo '</th>';

echo '<th class="right">';
echo $this->Paginator->sort('sum_price', __d('admin', 'Price'));
echo '</th>';

if (Configure::read('app.isDepositEnabled') && Configure::read('app.isDepositPaymentCashless')) {
    echo '<th>'.__d('admin', 'Deposit').'</th>';
}

if (count($pickupDay) == 1) {
    echo '<th>'.__d('admin', 'Picked_up').'</th>';
}

if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $appAuth->isSuperadmin()) {
    echo '<th>'.__d('admin', 'Invoice').'</th>';
}

?>