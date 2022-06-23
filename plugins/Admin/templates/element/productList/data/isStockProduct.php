<?php
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

if (!$advancedStockManagementEnabled) {
    return false;
}

echo '<td class="is-stock-product">';
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        if ($product->manufacturer->stock_management_enabled) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light product-is-stock-product-edit-button',
                    'title' => __d('admin', 'Is_stock_product'),
                    'escape' => false
                ]
            );
        }
        if ($product->is_stock_product) {
            echo '<i class="fas fa-check ok no-button"></i>';
        } else {
            echo '<i class="fas fa-times no-button"></i>';
        }
    }
echo '</td>';


?>