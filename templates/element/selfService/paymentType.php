<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    return false;
}

$cartsTable = FactoryLocator::get('Table')->get('Carts');
$paymentTypeAsString = __('Credit');
if ($paymentType == $cartsTable::CART_SELF_SERVICE_PAYMENT_TYPE_CASH) {
    $paymentTypeAsString =  __('Cash');
}

echo '<p class="payment-type">' .  __('Payment_type') . ': ' . $paymentTypeAsString . '</p>';

?>