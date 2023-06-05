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

$orderParam = '';
if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
    $orderParam = 'order=storageLocation&';
}
if (count($orderDetails) == 0) {
    $this->element('addScript', [ 'script' =>
        Configure::read('app.jsNamespace').".Helper.disableButton($('.generate-order-details-as-pdf'));"
    ]);
}
if (count($pickupDay) == 1 && $groupBy == 'customer' && ($appAuth->isSuperadmin() || $appAuth->isAdmin())) {
    echo '<a href="/admin/order-details/orderDetailsAsPdf.pdf?'.$orderParam.'pickupDay='.$pickupDay[0].'" target="blank" class="generate-order-details-as-pdf dropdown-item"><i class="fa-fw far fa-file-pdf"></i> '.__d('admin', 'Orders_as_pdf').'</a>';
}

?>