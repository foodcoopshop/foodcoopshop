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

echo '<td class="amount ' . (empty($product->product_attributes) && $product->stock_available->quantity <= 0 ? 'not-available' : '') . '">';

    if (empty($product->product_attributes)) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-quantity-edit-button',
                'title' => __d('admin', 'change_amount'),
                'escape' => false
            ]
        );
        echo '<span class="quantity-for-dialog">';
        echo $this->Number->formatAsDecimal($product->stock_available->quantity, 0);
        echo '</span>';
        if ($product->is_stock_product) {
            if ($product->stock_available->quantity_limit != 0) {
                echo ' <i class="small quantity-limit-for-dialog">';
                    echo $this->Number->formatAsDecimal($product->stock_available->quantity_limit, 0);
                echo '</i>';
            }
            if (is_null($product->stock_available->sold_out_limit) || $product->stock_available->sold_out_limit != 0) {
                echo ' / <i class="small sold-out-limit-for-dialog">';
                    if (is_null($product->stock_available->sold_out_limit)) {
                        echo '<i class="fas fa-times" title="'.__d('admin', 'No_email_notifications_are_sent_for_this_product.').'"></i>';
                    } else {
                        echo $this->Number->formatAsDecimal($product->stock_available->sold_out_limit, 0);
                    }
                echo '</i>';
            }
        }
    }

echo '</td>';

?>