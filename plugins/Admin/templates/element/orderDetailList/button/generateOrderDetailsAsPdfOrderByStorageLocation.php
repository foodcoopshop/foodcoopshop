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

if (!Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
    return;
}

if (count($pickupDay) == 1 && $groupBy == 'customer' && ($appAuth->isSuperadmin() || $appAuth->isAdmin())) {
    echo '<a href="/admin/order-details/orderDetailsAsPdf.pdf?order=storageLocation&pickupDay='.$pickupDay[0].'" target="blank" class="btn btn-outline-light generate-order-details-as-pdf"><i class="far fa-file-pdf"></i> '.__d('admin', 'Orders_as_pdf_order_by_storage_location').'</a>';
}

?>