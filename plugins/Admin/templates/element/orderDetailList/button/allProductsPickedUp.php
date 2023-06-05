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

$class = 'btn btn-outline-light';
if (isset($renderAsButtonInDropdown) && $renderAsButtonInDropdown) {
    $class = 'dropdown-item';
}

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
    echo '<button class="change-products-picked-up-all-customers-button ' . $class . '"><i class="far fa-check-square"></i> ' . __d('admin', 'All_products_picked_up?') . '</button>';
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
    echo '<button class="change-products-picked-up-button ' . $class . '"><i class="far fa-check-square"></i> ' . __d('admin', 'All_products_picked_up?') . '</button>';
}

?>