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

if (count($pickupDay) == 1 && $groupBy == 'customer' && ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isCustomer())) {
    $this->element('addScript', [ 'script' =>
        Configure::read('app.jsNamespace').".ModalOrderDetailAllProductsPickedUp.initPickedUpForAllCustomers();" .
        Configure::read('app.jsNamespace').".ModalOrderDetailAllProductsPickedUp.initPickedUpGroupedByCustomer();"
    ]);
    if (count($orderDetails) == 0) {
        $this->element('addScript', [ 'script' =>
            Configure::read('app.jsNamespace').".Helper.disableButton($('.change-products-picked-up-all-customers-button'));"
        ]);
    }
    echo '<button class="change-products-picked-up-all-customers-button btn btn-outline-light"><i class="far fa-check-square"></i> ' . __d('admin', 'All_products_picked_up?') . '</button>';
}

if (count($pickupDay) == 1 && $groupBy == '' && $customerId > 0 && $manufacturerId == '' && $productId == '') {
    $this->element('addScript', [ 'script' =>
        Configure::read('app.jsNamespace').".ModalOrderDetailAllProductsPickedUp.initNotGroupedBy();"
    ]);
    if (count($orderDetails) == 0) {
        $this->element('addScript', [ 'script' =>
            Configure::read('app.jsNamespace').".Helper.disableButton($('.change-products-picked-up-button'));"
        ]);
    }
    echo '<button class="change-products-picked-up-button btn btn-outline-light"><i class="far fa-check-square"></i> ' . __d('admin', 'All_products_picked_up?') . '</button>';
}

?>