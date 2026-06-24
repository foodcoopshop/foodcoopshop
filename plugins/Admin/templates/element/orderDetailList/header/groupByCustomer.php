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

echo '<th class="stretch">';
echo __('Member');
echo '</th>';

echo '<th>';
echo '</th>';

echo '<th class="right">';
echo $sortOrLabel('sum_price', __('Price'));
echo '</th>';

if (Configure::read('app.isDepositEnabled') && $this->Html->paymentIsCashless()) {
    echo '<th class="center">'.__('Deposit').'</th>';
}

if (count($pickupDay) == 1) {
    echo '<th>'.__('Picked_up').'</th>';
}

if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $identity->isSuperadmin()) {
    echo '<th>'.__('Invoice').'</th>';
}

?>