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

echo '<td class="delivery-rhythm">';

    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        
        if (!$product->is_stock_product) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'product-delivery-rhythm-edit-button',
                'title' => __d('admin', 'change_delivery_rhythm')
            ], 'javascript:void(0);');
        }
        
        if ($product->is_stock_product) {
            echo $this->Html->getDeliveryRhythmString('week', 1);
        } else {
            echo '<span class="delivery-rhythm-for-dialog">';
                echo '<span class="hide dropdown">'.$product->delivery_rhythm_count . '-' . $product->delivery_rhythm_type.'</span>';
                echo '<span class="delivery-rhythm-string">';
                    echo $this->Html->getDeliveryRhythmString($product->delivery_rhythm_type, $product->delivery_rhythm_count);
                echo '</span>';
                if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                    echo ', ';
                    if ($product->delivery_rhythm_type != 'individual') {
                        echo __d('admin', 'delivery_rhythm_from') . ' ';
                    }
                }
                echo '<span class="first-delivery-day">';
                    if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                        echo $this->Time->formatToDateShort($product->delivery_rhythm_first_delivery_day);
                    }
                echo '</span>';
                
                if ($product->delivery_rhythm_type == 'individual') {
                    echo ', ' . __d('admin', 'Order_possible_until') . ' ';
                    echo '<span class="order-possible-until">';
                        if (!is_null($product->delivery_rhythm_order_possible_until)) {
                            echo $this->Time->formatToDateShort($product->delivery_rhythm_order_possible_until);
                        }
                    echo '</span>';
                }
                
            echo '</span>';
        }
    }
        
echo '</td>';

?>