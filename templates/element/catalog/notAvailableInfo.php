<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

// render empty line is ok - to avoid jumping on attribute change
$notAvailableInfoText = '';
$availableQuantity = $stockAvailable['quantity'];
if ($product->is_stock_product && $product->manufacturer->stock_management_enabled) {
    $availableQuantity = $stockAvailable->quantity - $stockAvailable->quantity_limit;
}
if ((($product->is_stock_product && $product->manufacturer->stock_management_enabled) || !$stockAvailable->always_available) && $availableQuantity <= 0) {
    $notAvailableInfoText = __('Currently_not_on_stock').'.';
}
echo '<div class="line">
        <span class="not-available-info">'.$notAvailableInfoText.'</span>
    </div>';
