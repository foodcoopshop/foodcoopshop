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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<td class="' . (empty($product->product_attributes) && $product->stock_available->quantity <= 0 ? 'not-available' : '') . '">';

    if (empty($product->product_attributes)) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
            'class' => 'product-quantity-edit-button',
            'title' => __d('admin', 'change_amount')
        ], 'javascript:void(0);');
        echo '<span class="quantity-for-dialog">';
        echo $this->Number->formatAsDecimal($product->stock_available->quantity, 0);
        echo '</span>';
        if ($product->stock_available->quantity_limit != 0) {
            echo ' <i class="small quantity-limit-for-dialog">';
            echo $this->Number->formatAsDecimal($product->stock_available->quantity_limit, 0);
            echo '</i>';
        }
        if (!is_null($product->stock_available->sold_out_limit)) {
            echo ' / <i class="small sold-out-limit-for-dialog">';
            echo $this->Number->formatAsDecimal($product->stock_available->sold_out_limit, 0);
            echo '</i>';
        }
    }

echo '</td>';


?>