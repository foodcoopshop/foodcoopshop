<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (count($pickupDay) == 1 && $groupBy == 'customer' && ($appAuth->isSuperadmin() || $appAuth->isAdmin())) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".PickupDay.initChangeProductsPickedUpForAllCustomers('#order-details-list');" .
        Configure::read('app.jsNamespace') . ".Admin.initGenerateOrderDetailsAsPdf();"
    ]);
    echo '<button class="btn btn-outline-light generate-order-details-as-pdf"><i class="far fa-file-pdf"></i> '.__d('admin', 'Generate_orders_as_pdf').'</button>';
}

?>