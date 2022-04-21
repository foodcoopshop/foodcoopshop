<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') || $appAuth->isManufacturer()) {
    return false;
}

if (!empty($products)) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".ModalProductCalculateSellingPriceWithSurcharge.init();"
    ]);
    echo '<a id="calculateSellingPriceWithSurchargForSelectedProducts" class="btn btn-outline-light" href="javascript:void(0);"><i class="fas fa-calculator"></i> ' . __d('admin', 'Calculate_selling_price_with_surcharge_for_selected_products') . '</a>';
}

?>