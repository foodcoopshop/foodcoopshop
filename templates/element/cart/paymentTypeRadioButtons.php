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

$cartTable = FactoryLocator::get('Table')->get('Carts');
echo $this->Form->control('Carts.payment_type', [
    'label' => __('Payment_type'),
    'type' => 'radio',
    'options' => [
        $cartTable::CART_SELF_SERVICE_PAYMENT_TYPE_CASH => __('Cash'),
        $cartTable::CART_SELF_SERVICE_PAYMENT_TYPE_CREDIT => __('Credit')
    ],
    'disabled' => $appAuth->isSelfServiceCustomer(),
    'escape' => false,
]);

?>