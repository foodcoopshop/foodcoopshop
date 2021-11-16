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

echo '<td class="cell-surcharge">';
    if (empty($product->product_attributes)) {
        if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
            // surcharge for price per unit
        } else {
            echo '<b>' . $this->Number->formatAsPercent($product->surcharge_percent, 0) . '</b><br />';
            echo $this->Number->formatAsCurrency($product->surcharge_price);
        }
    }
echo '</td>';

?>