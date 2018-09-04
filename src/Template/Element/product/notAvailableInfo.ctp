<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

// render empty line is ok - to avoid jumping on attribute change
$notAvailableInfoText = '';
$availableQuantity = $stockAvailable['quantity'] - $stockAvailable['quantity_limit'];
if ($availableQuantity == 0) {
    $notAvailableInfoText = __('Currently_not_on_stock').'.';
}
echo '<div class="line">
        <span class="not-available-info">'.$notAvailableInfoText.'</span>
    </div>';
