<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<td class="delivery-rhythm">';

    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        
        if (!($product->manufacturer->stock_management_enabled && $product->is_stock_product)) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light product-delivery-rhythm-edit-button',
                    'title' => __d('admin', 'change_delivery_rhythm'),
                    'escape' => false
                ]
            );
        }
        
        $elementsToRender = [];
        
        if ($product->manufacturer->stock_management_enabled && $product->is_stock_product) {
            $elementsToRender[] = $product->delivery_rhythm_string;
            echo join(', ', $elementsToRender);
        } else {
            echo '<span class="delivery-rhythm-for-dialog">';
                
                echo '<span class="hide dropdown">'.$product->delivery_rhythm_count . '-' . $product->delivery_rhythm_type.'</span>';
                
                $deliveryDayElement = '<span class="first-delivery-day hide">';
                if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                    $deliveryDayElement .= $this->Time->formatToDateShort($product->delivery_rhythm_first_delivery_day);
                }
                $deliveryDayElement .= '</span>';
                echo $deliveryDayElement;
                
                
                $deliveryRhythmStringElement = '<span class="delivery-rhythm-string">' .
                        $product->delivery_rhythm_string . 
                    '</span>';
                $elementsToRender[] = $deliveryRhythmStringElement;
                
                $lastOrderWeekday = $this->Time->getNthWeekdayBeforeWeekday(1, $product->delivery_rhythm_send_order_list_weekday);
                $sendOrderListWeekdayElement = '<span class="send-order-list-weekday hide">';
                    $sendOrderListWeekdayElement .= $lastOrderWeekday;
                $sendOrderListWeekdayElement .= '</span>';
                echo $sendOrderListWeekdayElement;

                if ($product->delivery_rhythm_type != 'individual') {
                    if ($product->delivery_rhythm_send_order_list_weekday != $this->Time->getSendOrderListsWeekday()) {
                        $elementsToRender[] = __d('admin', 'Last_order_weekday') . ': ' . $this->Time->getWeekdayName($lastOrderWeekday) . ' ' . __d('admin', 'midnight');
                    }
                }
                
                if ($product->delivery_rhythm_type == 'individual') {
                    
                    $sendOrderListDayElement = '';
                    $sendOrderListDayElement .= __d('admin', 'Order_possible_until') . ' ';
                    $sendOrderListDayElement .= '<span class="order-possible-until">';
                    if (!is_null($product->delivery_rhythm_order_possible_until)) {
                        $sendOrderListDayElement .= $this->Time->formatToDateShort($product->delivery_rhythm_order_possible_until);
                    }
                    $sendOrderListDayElement .= '</span>';
                    $elementsToRender[] = $sendOrderListDayElement;
                    
                    if (!is_null($product->delivery_rhythm_send_order_list_day)) {
                        $elementsToRender[] = __d('admin', 'Send_order_lists_day') . ' ' . 
                            '<span class="send-order-list-day">' . 
                                $this->Time->formatToDateShort($product->delivery_rhythm_send_order_list_day) .
                        '</span>';
                    } else {
                        $elementsToRender[] = __d('admin', 'Order_list_is_not_sent');
                    }
                    
                }
                
                $deliveryDayElement = '';
                if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                    if ($product->delivery_rhythm_type != 'individual') {
                        $deliveryDayElement = __d('admin', 'delivery_rhythm_from') . ' ';
                    } else {
                        $deliveryDayElement = __d('admin', 'Delivery_day') . ': ';
                    }
                    if (!is_null($product->delivery_rhythm_first_delivery_day)) {
                        $deliveryDayElement .= $this->Time->formatToDateShort($product->delivery_rhythm_first_delivery_day);
                    }
                    $elementsToRender[] = $deliveryDayElement;
                }
                
                echo join(', ', $elementsToRender);
                
            echo '</span>';
        }
    }
        
echo '</td>';

?>