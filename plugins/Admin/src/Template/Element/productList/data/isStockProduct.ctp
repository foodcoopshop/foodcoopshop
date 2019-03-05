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
            echo '<i class="fas fa-check no-button"></i>';
        } else {
            echo '<i class="fas fa-times no-button"></i>';
        }
    }
echo '</td>';


?>