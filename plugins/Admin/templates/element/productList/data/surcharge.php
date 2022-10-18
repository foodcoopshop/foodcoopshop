<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<td class="cell-surcharge">';
    if (empty($product->product_attributes)) {
        if (isset($product->surcharge_percent) && $product->surcharge_percent > 0) {
            echo '<b>' . $this->Number->formatAsPercent($product->surcharge_percent, 1) . '</b><br />';
            echo $this->Number->formatAsCurrency($product->surcharge_price);
        }
    }
echo '</td>';

?>